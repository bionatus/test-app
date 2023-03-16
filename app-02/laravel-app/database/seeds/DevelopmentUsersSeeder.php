<?php

namespace Database\Seeders;

use App\Constants\Environments;
use App\Constants\Filesystem;
use App\Models\User;
use Hash;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Database\Seeder;
use Storage;

class DevelopmentUsersSeeder extends Seeder implements EnvironmentSeeder
{
    use SeedsEnvironment;

    private string $password;

    public function __construct()
    {
        $this->password = Hash::make('passwd321@_');
    }

    const EMAILS = [
        // Admin
        'gabriel.zanetti@devbase.us',
        'matias.velilla@devbase.us',
        'emilio.bottino@devbase.us',
        'joaquin.steffan@devbase.us',
        'jorge.moreno@devbase.us',
        // BE
        'alejandro.rohmer@devbase.us',
        'carlos.rojas@devbase.us',
        'diego.romero@devbase.us',
        'robert.lopez@devbase.us',
        'samir.fragozo@devbase.us',
        'fernando.ortuno@devbase.us',
        'facundo.condal@devbase.us',
        'damian.dicostanzo@devbase.us',
        // FE
        'fernando.keim@devbase.us',
        'josue.angarita@devbase.us',
        'matias.nasiff@devbase.us',
        'roni.castro@devbase.us',
        'thiago.corta@devbase.us',
        'marcos.godoy@devbase.us',
        'alexis.borges@devbase.us',
        'reubert.barbosa@devbase.us',
        'dev@example.com',
        // QA
        'constanza.giorgetti@devbase.us',
        'emilia.bejarano@devbase.us',
        'kevin.di.julio@devbase.us',
        'walter.nolasco@devbase.us',
        'lucas.vazquez@devbase.us',
        'carlos.viniales@devbase.us',
        'estanislao.larriera@devbase.us',
        'tomas.ricolfi@devbase.us',
        'qabluon@gmail.com',
    ];

    /**
     * @throws FileNotFoundException
     */
    public function run()
    {
        foreach (self::EMAILS as $email) {
            $user = $this->upsertUser($email);
            $this->upsertPhoto($user);
        }
    }

    private function upsertUser(string $email): User
    {
        if ($user = User::where('email', $email)->first()) {
            $user->email = strtolower($email);
            $user->save();

            return $user;
        }

        return $this->createUser($email);
    }

    private function createUser(string $email): User
    {
        $name      = strtok($email, '@');
        $firstName = ucfirst(strtok($name, '.'));
        $lastName  = $this->lastName();

        return User::create([
            'email'                     => $email,
            'email_verified_at'         => '2021-01-01',
            'first_name'                => $firstName,
            'last_name'                 => $lastName,
            'name'                      => $firstName . ' ' . $lastName,
            'password'                  => $this->password,
            'accreditated'              => true,
            'accreditated_at'           => '2021-01-01',
            'registration_completed'    => true,
            'registration_completed_at' => '2021-01-01',
            'access_code'               => rand(1000, 9999),
            'experience_years'          => rand(1, 10),
        ]);
    }

    private function lastName(): string
    {
        $lastName         = strtok('.');
        $compoundLastName = strtok('.');
        if ($compoundLastName) {
            $lastName .= ' ' . $compoundLastName;
        }

        return ucwords($lastName);
    }

    /**
     * @throws FileNotFoundException
     */
    private function upsertPhoto(User $user): void
    {
        if (!$user->photo || $user->photo !== $this->photoName($user->email)) {
            $this->storePhoto($user);
        }
    }

    /**
     * @throws FileNotFoundException
     */
    private function storePhoto(User $user): void
    {
        $sourceDisk = Storage::disk(Filesystem::DISK_DEVELOPMENT_MEDIA);
        $publicDisk = Storage::disk('public');
        $fileName   = $this->photoName($user->email);
        $filePath   = Filesystem::FOLDER_DEVELOPMENT_USERS_IMAGES . $fileName;

        if ($sourceDisk->exists($filePath) && !$publicDisk->exists($fileName)) {
            $publicDisk->put($fileName, $sourceDisk->get($filePath));
        }

        $user->photo = $fileName;
        $user->save();
    }

    private function photoName($email): string
    {
        return strstr($email, '@', true) . '.jpeg';
    }

    public function environments(): array
    {
        return Environments::ALL_BUT_PRODUCTION_OR_TESTING;
    }
}
