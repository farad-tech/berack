<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('user:promote-admin {email : The email address of the user to promote}')]
#[Description('Grant admin panel access to an existing user')]
class PromoteUserToAdmin extends Command
{
    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');
        $user = User::where('email', $email)->first();

        if ($user === null) {
            $this->error("User [{$email}] was not found.");

            return self::FAILURE;
        }

        $user->forceFill([
            'is_admin' => true,
        ])->save();

        $this->info("User [{$email}] can now access the admin panel.");

        return self::SUCCESS;
    }
}
