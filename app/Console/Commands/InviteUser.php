<?php

namespace App\Console\Commands;

use Exception;
use App\Exceptions\Auth0Exception;
use Illuminate\Support\Str;
use App\Mail\NewUserMail;
use App\Models\Auth\User;
use App\Models\ShareInvites;
use App\Services\Auth0\Facade\Auth0Service;
use App\Services\Auth0\Facade\UserService;
use Illuminate\Console\Command;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class InviteUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'invite:user {--email=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This Command Send Inviation to the application';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {

        $email = $this->option('email');
        if(!$email) {
            $this->info('email and businessId is required');
            return Command::INVALID;
        }

        $user = User::whereEmail($email)->first();

        if(!$user) {
            $this->info('User not found!');
            return Command::FAILURE;
        }

        $this->line('Process Invite User');

        DB::beginTransaction();

        try {
            $createdAuth0Id = $user?->auth0 ?? null;

            if($createdAuth0Id) {
                Auth0Service::deleteUser($createdAuth0Id);
            }

            $password = 'Fxp@' . Str::random(8) . '0!';

            $userPayload = [
                'user' => [
                    'email' => $user['email'],
                    'first_name' => $user['first_name'],
                    'last_name' => $user['last_name'],
                    'password' =>  $password,
                    'phone_number' => null,
                    'new' => false
                ],
                'roles' => [
                    'invited client' //invited by the agent, role will be this default
                ]
            ];

            $createdAuth0Id = null;

            $userPayload['user']['new'] = true;
            $response = UserService::create($userPayload, false, true);

            if (!empty($response) && $response['code'] !== Response::HTTP_CREATED) {
                throw new Auth0Exception($response['message'], $response['code']);
            }

            $userId = $response['data']['user_id'];

            $createdAuth0Id = $response['data']['auth0'];

            $payload['client_id'] = $userId;


            Mail::to(['email' => $email])->send(new NewUserMail([
                'first_name' => $user['first_name'],
                'last_name' => $user['last_name'],
                'email' => $user['email'],
                'password' => 'Fxp@' . Str::random(8) . '0!',
                'url' => UserService::getResetPasswordLink($createdAuth0Id)
            ]));

            DB::commit();

            $this->info('Invitation sent successfully!');
        } catch (Exception $e) {
            DB::rollBack();
            info($e);

            if ($createdAuth0Id) {
                Auth0Service::deleteUser($createdAuth0Id);
            }

            $this->info('Invitation failed!');
        }
    }
}
