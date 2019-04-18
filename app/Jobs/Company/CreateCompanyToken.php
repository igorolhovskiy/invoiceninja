<?php

namespace App\Jobs\Company;

use App\Models\Company;
use App\Models\CompanyToken;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CreateCompanyToken implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $company;

    protected $user;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Company $company, User $user)
    {
        $this->company = $company;
        
        $this->user = $user;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle() : ?CompanyToken
    {

        $ct = CompanyToken::create([
            'user_id' => $this->user->id,
            'account_id' => $this->company->account->id,
            'token' => str_random(64),
            'name' => $this->user->first_name. ' '. $this->user->last_name,
            'company_id' => $this->company->id,
        ]);
        
        return $ct;
    }
}