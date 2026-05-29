<?php

namespace App\Console\Commands;

use App\Models\Staff;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class CheckUserCommand extends Command
{
    protected $signature = 'bms:check-user
                            {email : Login email to look up}
                            {--password= : Optionally verify a password (not echoed)}';

    protected $description = 'Check whether a login account exists in the current database';

    public function handle(): int
    {
        $email = strtolower(trim((string) $this->argument('email')));

        if ($email === '' || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->error('Provide a valid email address.');

            return self::FAILURE;
        }

        $user = User::query()->where('email', $email)->first();

        if (! $user) {
            $this->error("No user found for: {$email}");
            $this->line('Database: '.$this->databaseLabel());
            $this->newLine();
            $this->line('Similar emails:');
            foreach ($this->similarEmails($email) as $similar) {
                $this->line("  - {$similar}");
            }

            return self::FAILURE;
        }

        $staff = Staff::query()->where('user_id', $user->id)->first()
            ?? Staff::query()->where('email', $email)->first();

        $this->info("User found: {$email}");
        $this->table(
            ['Field', 'Value'],
            [
                ['Database', $this->databaseLabel()],
                ['ID', (string) $user->id],
                ['Name', $user->name],
                ['Email', $user->email],
                ['Role', $user->role],
                ['Branch', $user->branch ?? '—'],
                ['Password set', $user->password ? 'yes' : 'no'],
                ['Force password change', $user->force_password_change ? 'yes' : 'no'],
                ['Staff record', $staff ? "#{$staff->id} (user_id: ".($staff->user_id ?? 'null').')' : 'none'],
                ['Staff linked', ($staff && (int) $staff->user_id === (int) $user->id) ? 'yes' : 'no'],
                ['Created', optional($user->created_at)?->toDateTimeString() ?? '—'],
            ]
        );

        $password = $this->option('password');
        if ($password !== null && $password !== '') {
            $valid = Hash::check($password, $user->password);
            $this->line($valid
                ? '<fg=green>Password check: MATCH</>'
                : '<fg=red>Password check: NO MATCH</>');
        } else {
            $this->line('Tip: add --password="your-password" to test login credentials without changing them.');
        }

        return self::SUCCESS;
    }

    private function databaseLabel(): string
    {
        $connection = config('database.default');
        $database = config("database.connections.{$connection}.database");

        return "{$connection} / {$database}";
    }

    /** @return list<string> */
    private function similarEmails(string $email): array
    {
        $local = strstr($email, '@', true) ?: $email;

        return User::query()
            ->where('email', 'like', "%{$local}%")
            ->orderBy('email')
            ->limit(10)
            ->pluck('email')
            ->all();
    }
}
