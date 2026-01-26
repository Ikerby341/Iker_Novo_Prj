# Iker_Novo_Prj

Descripció curta
-----------------
Aquest projecte és una aplicació PHP senzilla per gestionar una llista d'articles (cotxes) amb vista pública, paginació, i funcionalitats d'autenticació i CRUD. Està pensat com a pràctica i com a punt de partida per un projecte més gran.

Funcionalitats principals
-------------------------
- Autenticació d'usuaris
  - Pàgina de `login` i `register` amb validació al servidor.
  - Les contrasenyes es guarden amb `password_hash()` (BCRYPT) i es verifica amb `password_verify()`.
  - Validació de força de contrasenya: mínim 7 caràcters, majúscula, minúscula i símbol.
  - Migració transparent si alguna contrasenya es troba en text pla: es rehash al primer login.
  - Opció "Recorda'm" (remember me): genera un token, el desa a BD i el guarda a la cookie HttpOnly per restaurar sessió.
  - Recuperació de contrasenya perduda: genera un token únic i envia un email amb link de resetatge via SMTP.
  - Canvi de contrasenya: permet als usuaris autenticats canviar la seva contrasenya.

- Protecció amb Google reCAPTCHA v2
  - Formularis `login` i `register` inclouen el widget client-side.
  - Verificació server-side mitjançant la clau secreta en `config/recaptcha.php`.

- Gestió de perfil d'usuari
  - Editar perfil: permet modificar nom d'usuari i email amb validació de duplicats.
  - Canviar contrasenya: permet actualitzar la contrasenya actual amb validació de força.
  - Els canvis es reflecteixen immediatament a la sessió activa.

- Gestió d'usuaris (admin)
  - Pàgina d'administració per usuaris amb rol admin.
  - Veure llistat complet d'usuaris registrats.
  - Editar dades d'altres usuaris (nom, email, estat admin).
  - Eliminar usuaris (només si no són admin).
  - Promocionar/degradar usuaris a/des de rol admin.

- CRUD
  - Crear, llegir, actualitzar i eliminar articles (cotxes) amb control d'accés basat en propietat (owner_id).
  - Les operacions d'editar i esborrar es fan via formularis POST per evitar dependència de JS.

- Paginació i controls
  - Paginació d'articles amb paràmetres `page` i `per_page` gestionats des del controlador.
  - Controls d'ordenació (`sort`, `dir`) amb whitelist per prevenir injeccions SQL.
  - Control d'entrada lliure per `Articles per pàgina` que manté la paginació i els paràmetres d'ordenació.

- Gestió de rutes
  - El projecte utilitza una constant `BASE_URL` per construir enllaços i rutes dins de les vistes i includes. Això evita rutes absolutes que depenguin d'una màquina o d'una estructura concreta de carpetes.
  - Per què s'ha creat: perquè l'aplicació funcioni correctament des de qualsevol màquina (localhost, un directori dins d'un servidor, o un entorn amb domini propi) sense haver de canviar manualment tots els enllaços de les vistes (Vaig haver d'afegir-ho ja que des de l'ordinador de casa no funcionaba i vaig veure que sense això depenia molt de que les rutes siguin les mateixes que les del meu portatil i això no ha de ser així).

Estructura de fitxers rellevant
-------------------------------
- `index.php` (entrada pública / vista principal)
- `app/Controller/controlador.php` — archivo principal que orquesta l'inclusió de tots els controllers específics.
- `app/Controller/captcha_controller.php` — verificació de Google reCAPTCHA v2 (server-side).
- `app/Controller/session_controller.php` — gestió de sessions, timeout i restauració amb remember-me.
- `app/Controller/auth_controller.php` — autenticació, registre, validació de contrasenyes i recuperació de contrasenya perduda.
- `app/Controller/pagination_controller.php` — paginació, ordenació i paràmetres de visualització.
- `app/Controller/articles_controller.php` — visualització i manipulació d'articles.
- `app/Controller/crud_controller.php` — gestió de creació, edició i eliminació d'articles, perfil d'usuari i gestió admin.
- `app/Controller/users_controller.php` — operacions sobre usuaris i rols admin.
- `app/Controller/SmtpMailer.php` — classe personalitzada per enviar emails via SMTP (recuperació de contrasenya).
- `app/Model/users_model.php` — funcions d'accés a la base de dades per usuaris (inclou token remember i recuperació de contrasenya).
- `app/Model/articles_model.php` — funcions d'accés a la base de dades per articles.
- `app/View/` — vistes PHP: `vista.php`, `login.php`, `register.php`, `create.php`, `update.php`, `delete.php`, `editprofile.php`, `changepassword.php`, `forgotpassword.php`, `resetpassword.php`, `admin.php`, `edit_user.php`.
- `config/recaptcha.php` — defineix `RECAPTCHA_SECRET`.
- `config/phpmailer.php` — configuració SMTP per enviar emails.
- `resources/styles/style.css` — estils principals del projecte.

Credencials Administrador
-------------------------
- User: admin
- Pass: Proba1234.