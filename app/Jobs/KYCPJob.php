<?php

namespace App\Jobs;

use App\Services\KYCP\Facades\KYCP;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\Response;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

class KYCPJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public $tries = 3;

    /**
     * Request Payload
     *
     */
    protected mixed $args;

    /**
     * KYCP method to executed
     *
     */
    protected string $kycpMethod;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(string $kycpMethod, mixed ...$args)
    {
        $this->args = $args;
        $this->kycpMethod = $kycpMethod;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            $method = $this->kycpMethod;
            $response = KYCP::$method(...$this->args);

            if ($response->status() >= 400) {
                throw new HttpException($response->status(), $response->body());
            }

            $content = $response->json();
            if ($content['Success'] === false) {
                throw new HttpException(Response::HTTP_BAD_REQUEST, $response->body());
            }
        } catch (Throwable $error) {
            // retry after 1 minute
            $this->release(60);
            throw $error;
        }
    }
}
