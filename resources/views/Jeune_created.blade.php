<p>Bonjour {{ $user->prenom }} {{ $user->nom }},</p>

<p>Nous sommes heureux de vous informer que votre compte a été créé avec succès !</p>

<p>Pour confirmer votre inscription et finaliser la création de votre compte, veuillez cliquer sur le bouton ci-dessous :</p>

{{-- <p style="text-align: center;">
    <a href="{{ url('/confirm-inscription') }}?token={{ $user->confirmation_token }}"
       style="display: inline-block; padding: 10px 20px; font-size: 16px; color: #fff; background-color: #007BFF; text-decoration: none; border-radius: 5px;">
        Confirmer mon inscription
    </a>
</p> --}}

<p style="text-align: center;">
    <a href="http://localhost:4200/authentication/login?token={{ $user->confirmation_token }}"
       style="display: inline-block; padding: 10px 20px; font-size: 16px; color: #fff; background-color: #007BFF; text-decoration: none; border-radius: 5px;">
        Confirmer mon inscription
    </a>
</p>


<p>Voici vos informations de connexion :</p>

<ul>
    <li><strong>Email :</strong> {{ $user->email }}</li>
    <li><strong>Mot de passe par défaut :</strong> passer123</li>
</ul>

<p>Une fois votre inscription confirmée, nous vous invitons à compléter votre profil pour que nous puissions mieux vous connaître et vous offrir une expérience personnalisée.</p>

<p>Merci de faire partie de notre communauté !</p>


