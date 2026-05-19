# Comptes et mots de passe — Moncine

## Pour les utilisateurs

- **Mon compte** (`/mon-compte.php`) : modifier le nom, l’e-mail et le mot de passe.
- **Mot de passe oublié** (`/mot-de-passe-oublie.php`) : recevoir un lien par e-mail (valable 1 heure).
- Un administrateur peut aussi vous donner un **mot de passe provisoire** ; changez-le ensuite dans Mon compte.

## Pour l’administrateur (YunoHost)

### Envoi des e-mails

La réinitialisation utilise la fonction PHP `mail()`. Sur YunoHost, configurez l’envoi de mails du serveur (ex. `postfix`) ou définissez :

| Variable | Rôle |
|----------|------|
| `MONCINE_MAIL_FROM` | Adresse expéditeur (ex. `moncine@votredomaine.fr`) |
| `MONCINE_BASE_URL` | URL publique de l’app (ex. `https://moncine.example.net`) si le lien dans l’e-mail est incorrect |

Sans serveur mail fonctionnel, les utilisateurs peuvent demander à l’admin un **Réinit. MDP** depuis la page Comptes.

### Migration base

Après mise à jour du paquet :

```bash
php lib/cli/migrate.php
```

Cela crée la table `password_reset_tokens` (migration `004`).

### Sécurité

- Limite de tentatives sur la connexion et sur « mot de passe oublié ».
- Jetons stockés **hachés** en base ; usage unique.
- Message neutre si l’e-mail n’existe pas (pas d’énumération des comptes).
