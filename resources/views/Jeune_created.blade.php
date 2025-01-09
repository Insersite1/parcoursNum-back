<!-- resources/views/emails/user_created.blade.php -->
<p>Bonjour {{ $user->nom }},</p>

<p>Nous sommes heureux de vous informer que votre compte a été créé avec succès !</p>

<p>Pour confirmer votre inscription et finaliser la création de votre compte, veuillez cliquer sur le lien ci-dessous :</p>

{{-- <p><a href="{{ url('api/confirm-inscription') }}?token={{ $user->confirmation_token }}">Confirmer mon inscription</a></p> --}}

<p>Une fois votre inscription confirmée, nous vous invitons à compléter votre profil pour que nous puissions mieux vous connaître et vous offrir une expérience personnalisée.</p>

<p>Merci de faire partie de notre communauté !</p>
