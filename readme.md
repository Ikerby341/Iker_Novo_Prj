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
  - Per què s'ha creat: perquè l'aplicació funcioni correctament des de qualsevol màquina (localhost, un directori dins d'un servidor, o un entorn amb domini propi) sense haver de canviar manualment tots els enllaços de les vistes (Vaig haver d'afegir-ho ja que des de l'ordinador de casa no funcionaba i vaig veure que sense això depenguin molt de que les rutes siguin les mateixes que les del meu portatil i això no ha de ser així).

Novetats recents
----------------
- Entrades OAuth centralitzades: s'ha afegit un petit enrutador a `public/index.php` per manejar les accions `?action=github_login` i `?action=github_callback`. Això evita tenir fitxers públics separats per a cada callback i manté `public/` més net.
- Integració GitHub amb Hybridauth: s'ha afegit suport per autenticar amb GitHub mitjançant la llibreria Hybridauth i la configuració a `config/hybridauth.php`. La URL de callback per defecte apunta a `public/index.php?action=github_callback` i la variable `GITHUB_REDIRECT_URI` es pot sobreescriure a `.env`.
- OAuth amb Discord: hi ha un flux OAuth específic implementat manualment per Discord (`app/Controller/oauth_callback.php`) i una vista d'ajust per completar registre amb dades de Discord (`app/View/register_discord.php`).
- Comentaris traduïts: s'han traduït a català diversos comentaris del codi per millorar la coherència de l'idioma dins del projecte.
- Generació de vehicles aleatoris: s'ha afegit un botó a la pàgina de creació d'articles (`create.php`) que consumeix l'API pública de NHTSA (National Highway Traffic Safety Administration) per obtenir marques i models de vehicles aleatoris. Filtra marques comunes a Espanya (com Audi, Seat, BMW, etc.) per proporcionar dades realistes.

API (`api.php`)
------------------
S'ha afegit una API senzilla en `api.php` per consultar dades dels vehicles en format JSON. Aquesta API permet consumir la informació des de clients externs (per exemple, JavaScript o altres aplicacions).

- Format de resposta: JSON  
- Mètode suportat: GET  
- CORS habilitat (es permeten peticions externes)
- **Autenticació requerida**: Tota petició requereix una API key vàlida de la base de dades.

### Autenticació per API Key
Cada endpoint requereix autenticació. Pots passar la API key de tres formes:

1. **Paràmetre de query (`api_key`):**
   ```
   GET /api/vehicles?api_key=TU_API_KEY
   ```

2. **Header `X-API-KEY`:**
   ```
   GET /api/vehicles
   Header: X-API-KEY: TU_API_KEY
   ```

3. **Header `Authorization` amb `Bearer`:**
   ```
   GET /api/vehicles
   Header: Authorization: Bearer TU_API_KEY
   ```

Si la API key no és vàlida o no es proporciona, retorna un error `401 Unauthorized`.

### Endpoints disponibles
- `GET /api/vehicles`  
  Retorna tots els vehicles disponibles.

- `GET /api/users/{id}/vehicles`  
  Retorna els vehicles associats a un usuari concret.

### Notes
- Només s'accepten peticions GET (altres mètodes retornen error).  
- Si l'endpoint no existeix, retorna un error 404.  
- La API key es pot generar/regenerar des de la pàgina d'edició de perfil de l'usuari.
- Cada API key està associada a un usuari específic a la base de dades.
- Internament utilitza les funcions del model (`articles_model.php`) per obtenir les dades.  

Comparativa: OAuth amb Discord (manual) vs Hybridauth amb GitHub
-------------------------------------------------------------
- Implementació:
  - Discord: s'ha implementat manualment l'intercanvi de codi per token (petició POST a `https://discord.com/api/oauth2/token`) i la consulta de l'usuari (`/api/users/@me`) en `app/Controller/oauth_callback.php`.
  - GitHub: s'utilitza la llibreria Hybridauth per encapsular el procés d'autenticació OAuth i la gestió dels adaptadors (provider) en `app/Controller/github_login.php` i `app/Controller/github_callback.php`.
- Mantenibilitat i abstracció:
  - Discord (manual): codi explícit i senzill d'entendre, però cal gestionar directament tokens, errors i peticions HTTP; si s'afegeix més providers, caldrà replicar lògica.
  - GitHub (Hybridauth): Hybridauth ofereix abstracció i uniformitza la interacció amb múltiples providers (errors, obtenció de perfil, reconnects). A canvi afegeix una dependència externa i una petita corba d'aprenentatge.
- Personalització:
  - Discord: control total sobre com es processe el perfil i com es crea/enllaça l'usuari (més flexible a nivell específic del provider).
  - Hybridauth: facilita la feina comuna (autenticació, obtenció de perfil), però per comportaments molt específics encara cal adaptar el codi després d'obtenir el perfil.
- Requisits de configuració:
  - Discord: només necessites `DISCORD_CLIENT_ID`, `DISCORD_CLIENT_SECRET` i `DISCORD_REDIRECT_URI` a `.env`.
  - GitHub/Hybridauth: a més dels `GITHUB_CLIENT_ID`, `GITHUB_CLIENT_SECRET` i `GITHUB_REDIRECT_URI` a `.env`, hybridauth també requereix `composer` i la carpeta `vendor/`.

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

Correccions PROJECTE FASE 3
---------------------------
### 1. Correcció de seguretat en autenticació
- **Problema identificat**: La funció `login_user` a `auth_controller.php` incloïa una comparació de contrasenyes en text pla per suportar migracions d'usuaris amb contrasenyes antigues no hashejades. Això implicava comparar la contrasenya introduïda (en clar) amb el valor emmagatzemat a la base de dades, creant un risc de seguretat.
- **Solució implementada**: S'ha eliminat la lògica de comparació en text pla. Ara, només es verifica amb `password_verify()` si el hash és vàlid. Usuaris amb contrasenyes antigues en text pla no podran iniciar sessió i hauran d'utilitzar la funcionalitat de "recuperar contrasenya" per restablir-la de manera segura.
- **Impacte**: Millora la seguretat eliminant exposició de contrasenyes en clar. Usuaris afectats poden recuperar l'accés via email. Es recomana executar un script de migració separat per rehashejar contrasenyes antigues abans de desplegar en producció.

### 2. Protecció contra eliminació d'usuaris
- **Funcionament actual**: La protecció contra auto-eliminació i accés no autoritzat està implementada a nivell d'interfície d'usuari (UI). A `admin.php`, la llista d'usuaris filtra l'usuari actual amb `array_filter()` per evitar que un admin es pugui eliminar a si mateix. A més, l'accés a la vista està restringit només a usuaris amb rol admin via `is_admin()`. La funció `delete_user()` del model no té comprovacions internes i depèn dels controladors per validar permisos.
- **Problema identificat**: Si `delete_user()` es crida directament des d'un altre lloc (per exemple, scripts externs o futures extensions), podria permetre eliminacions no autoritzades. La lògica de seguretat depèn de qui crida la funció, no de la funció en si.
- **Modificació implementada**: S'han afegit comprovacions internes a `delete_user()` al model. Ara la funció requereix `current_user_id` i `is_admin` com a paràmetres, i verifica que només admins puguin eliminar i que no es pugui eliminar a si mateix.

### 3. Integritat referencial de la base de dades (ON DELETE CASCADE)
- **Problema identificat**: Les taules relacionades amb `usuarios` no tenien claus forànies amb `ON DELETE CASCADE`, de manera que en eliminar un usuari els seus registres associats quedaven orfes a la base de dades.
- **Solució implementada**: S'ha afegit una clau forana amb `ON DELETE CASCADE` a la taula de vehicles sobre la columna `owner_id`, referenciant `usuarios(id)`. Ara, quan s'elimina un usuari, tots els seus registres associats s'eliminen automàticament.
- **Impacte**: Millora la integritat referencial de la base de dades i evita l'acumulació de dades orfes. L'eliminació d'usuaris és ara una operació neta i consistent a nivell de base de dades.

### 4. Verificació de funcions OAuth
- **Comentari del professor**: Es va qüestionar si `login_user_oauth()` està definida, ja que s'utilitza a `github_callback.php` i `oauth_callback.php` però no apareix als fitxers adjunts.
- **Verificació**: La funció està correctament definida a `auth_controller.php` (línia 166) i és inclosa als fitxers que l'utilitzen amb `require_once`. No hi ha cap error, pot ser que quan ho vas mirar tinguesis una versió antiga del codi.

### 5. Protecció CSRF en OAuth Discord
- **Problema identificat**: El flux OAuth manual de Discord no validava el paràmetre `state`, exposant a atacs CSRF on un atacant podria interceptar el `code` i fer peticions falses al callback.
- **Solució implementada**: S'ha afegit generació i validació de `state` al flux de Discord. Als formularis `login.php` i `register.php`, es genera un `state` aleatori, s'emmagatzema a `$_SESSION['oauth_state']`, i s'envia amb la URL d'autorització. Al callback (`oauth_callback.php`), es valida que el `state` rebut coincideixi amb el de la sessió abans de processar el `code`. Si no coincideix, es redirigeix amb error.
- **Comparativa amb Hybridauth**: Hybridauth gestiona `state` automàticament, mentre que la implementació manual requeria aquest afegit per igualar la seguretat.

### 6. Gestió de registres incomplets en OAuth Discord
- **Comentari del professor**: Es va qüestionar si tancar `register_discord.php` a mitges deixa usuaris a mitges a la base de dades.
- **Explicació**: No, el registre és atòmic. Les dades de Discord s'emmagatzemen temporalment a la sessió (`$_SESSION['discord_data']`), però l'usuari només es crea a la BD quan es completa el formulari amb èxit. Si l'usuari tanca la finestra, no es crea res persistent, evitant dades orfes.

### 7. Exposició de clau secreta de reCAPTCHA
- **Error identificat pel professor**: La clau secreta de reCAPTCHA estava exposada en `config/recaptcha.php`, un fitxer que va a GitHub, mentre que les credencials OAuth estaven correctament al `.env`.
- **Correcció implementada**: S'ha eliminat `recaptcha.php` i mogut la clau a `.env` com `RECAPTCHA_SECRET`. S'han actualitzat `captcha_controller.php` i `controlador.php` per utilitzar `getenv('RECAPTCHA_SECRET')` en comptes de la constant definida. Això evita exposar secrets al repositori.
- **Nota**: Aixó va ser alguna confusió meva, les claus secretes haurien d'estar sempre al `.env` per seguretat.

### 8. Rutes hardcoded en oauth_callback.php
- **Problema identificat pel professor**: Les redireccions a `oauth_callback.php` utilitzen rutes absolutes hardcoded (`/Practiques/Backend/Iker_Novo_Prj/app/View/...`) en comptes d'utilitzar `BASE_URL`, cosa que fa que no funcioni en altres màquines.
- **Correcció implementada**: S'han substituït les rutes hardcoded per `BASE_URL . 'app/View/...'`, amb fallback si `BASE_URL` no està definit. Ara les redireccions són portables.
- **Nota**: Aquest era un error meu; havia de fer servir `BASE_URL` consistentment, però vaig tenir problemes inicials i vaig deixar les rutes hardcoded. Ara està corregit.

### 9. Simplificació creació d'usuaris OAuth GitHub
- **Problema identificat**: El codi de `github_callback.php` intentava crear un usuari OAuth de GitHub amb fins a tres intents consecutius, provant diferents combinacions de camps i columnes. Aquesta estratègia amagava problemes d'esquema i dificultava la depuració.
- **Solució implementada**: S'ha revisat l'estructura de la taula `usuarios` (veure `usuarios.sql`) i ara la creació d'usuaris OAuth GitHub es fa amb una sola inserció clara, assegurant els camps obligatoris (`username`, `email`, `password` - es deixa buida per OAuth, `github_id`). S'ha eliminat la lògica de múltiples fallbacks i s'ha simplificat el procés, millorant la robustesa i mantenibilitat del codi.

### 10. Gestió de múltiples tokens "remember me"
- **Problema identificat**: Si dues pestanyes obren la web alhora i restauren la sessió amb "remember me", la primera canvia el token i la segona troba el token antic, resultant en un error de restauració.
- **Solució**: Permetre múltiples tokens per usuari. En comptes de guardar un únic token a la base de dades, es crea una taula de tokens (amb usuari_id, token, data de creació i expiració). Cada login amb "remember me" genera un nou token i s'afegeix a la llista. Així, cada pestanya/dispositiu pot tenir el seu token vàlid independentment, evitant conflictes i millorant la seguretat i experiència d'usuari.
- **Nota**: Simplement sería fer aixó però ara mateix si em poso a fer aixó hauria de canviar l'estructura de la BBDD i moltes línies del meu codi.

### 11. Logout via GET vulnerable a CSRF
- **Problema identificat**: El logout es feia via GET (`index.php?logout=1`), cosa que permetia a un atacant forçar el logout d'un usuari amb una simple imatge (`<img src="index.php?logout=1">`). Això era una vulnerabilitat CSRF.
- **Solució implementada**: Ara el logout només es pot fer via POST i està protegit amb un token CSRF. Així només es pot executar des d'un formulari legítim de la pròpia web, evitant atacs externs.

### 12. Autenticació social (OAuth) i contrasenya
- **Pràctica habitual**: Moltes webs famoses (Discord, Twitter, Microsoft, etc.) permeten crear un compte amb OAuth (Google, Facebook, etc.) i, posteriorment, afegir una contrasenya des de la configuració del compte. Això permet iniciar sessió tant amb OAuth com amb email/contrasenya, però només si l'usuari ha establert una contrasenya manualment després del registre social.
- **Com està implementat aquí**: En aquest projecte, si un usuari es registra amb OAuth (ex: GitHub o Discord), el compte es crea sense contrasenya. Si després vol iniciar sessió amb email/contrasenya, primer ha d'establir una contrasenya (per exemple, mitjançant la funcionalitat de "recuperar contrasenya" o des de la configuració del perfil). Això segueix la pràctica habitual de grans plataformes i evita conflictes d'autenticació.

### 13. Separació de capes: model crida funcions del controlador
- **Problema identificat**: El model `articles_model.php` cridava funcions del controlador..
- **Solució implementada**: S'han modificat les funcions del model (`generar_articles`, `generar_paginacio`, `obtenir_total_pagines`, `listar_tots_articles`) per rebre paràmetres (`$is_logged_in`, `$user_id`) en comptes de cridar funcions del controlador. Ara el controlador passa aquests valors quan crida les funcions del model, mantenint la separació de capes.

### 14. Consultes SQL directes a la vista resetpassword.php
- **Problema identificat**: La vista `resetpassword.php` feia consultes SQL directes (validar token i actualitzar contrasenya), saltant-se el model i el controlador, violant l'arquitectura MVC.
- **Solució implementada**: S'han mogut les consultes SQL al model `users_model.php` creant les funcions `validate_reset_token()` i `reset_user_password()`. S'han creat funcions del controlador `validate_reset_token_controller()` i `reset_password_controller()` a `auth_controller.php` que criden al model. La vista ara crida al controlador, mantenint la separació de capes MVC completa.

### 15. Processament de formularis POST i redireccions directes a les vistes admin.php i edit_user.php
- **Problema identificat**: Les vistes `admin.php` i `edit_user.php` processaven directament els formularis POST, cridaven funcions del model (`get_all_users()`) i feien redireccions amb `header()`.
- **Solució implementada**: S'han creat funcions del controlador `admin_page_controller()` i `edit_user_page_controller($user_id)` a `crud_controller.php` que gestionen tot el processament de POST, les redireccions i retornen les dades necessàries per a les vistes. Les vistes ara només criden aquestes funcions del controlador i mostren les dades retornades, mantenint la separació de capes MVC.

AJAX
----
- He afegit millores amb AJAX a dues parts del projecte sense canviar la lògica de negoci del model/controlador.
- A `app/View/create.php`, el botó **Generar Vehicle Aleatori des de API** fa una petició `fetch()` al mateix fitxer i rep una resposta JSON amb `marca` i `model`. Això omple els camps del formulari sense recarregar tota la pàgina.
- A `app/View/editprofile.php`, el botó **Generar nova API key** també fa una petició AJAX a la mateixa pàgina i actualitza el camp readonly de l'API key amb la nova clau.
- També es manté la compatibilitat sense JavaScript: si no hi ha `fetch()`, els botons segueixen funcionant com abans amb formularis POST normals.