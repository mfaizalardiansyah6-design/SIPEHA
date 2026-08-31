<x-app-layout title="Profile">
    <x-page-header title="Profile" description="Kelola informasi akun dan keamanan Anda" />

    <div class="max-w-3xl space-y-6">
        <div class="card card-padding">
            @include('profile.partials.update-profile-information-form')
        </div>

        <div class="card card-padding">
            @include('profile.partials.update-password-form')
        </div>

        <div class="card card-padding">
            @include('profile.partials.delete-user-form')
        </div>
    </div>
</x-app-layout>
