# TubeYou — Instrukcja hostowania w sieci LAN (Windows + Apache/XAMPP)

> **Uwaga dot. ścieżek:** Instrukcja używa litery `G:\` jako przykładu pendrive'a.  
> Jeśli Twój pendrive ma inną literę (np. `E:\`, `F:\`), zamień `G:` wszędzie poniżej.  
> Aby pendrive zawsze miał tę samą literę → patrz sekcja **Troubleshooting: pendrive zmienił literę**.

---

## Wymagania

| Składnik | Wersja | Gdzie pobrać |
|----------|--------|--------------|
| XAMPP | najnowszy (PHP 8.1+) | https://www.apachefriends.org |
| ffmpeg | release essentials | https://www.gyan.dev/ffmpeg/builds |
| Composer | najnowszy | https://getcomposer.org |
| Git (opcjonalnie) | najnowszy | https://git-scm.com |

> **XAMPP i ffmpeg instalujemy na `C:\`, nie na pendrivie.**  
> Pendrive jest zbyt wolny do przetwarzania wideo i uruchamiania binarek.

---

## Krok 1 — Instalacja XAMPP

1. Pobierz i zainstaluj XAMPP w `C:\xampp`
2. Uruchom **XAMPP Control Panel jako Administrator** (prawy klik → Uruchom jako administrator)
3. Kliknij **Start** przy **Apache** i **MySQL**
4. Zielone tło przy obu = działają poprawnie

---

## Krok 2 — Konfiguracja Apache

### 2a. Włącz mod_rewrite

Otwórz `C:\xampp\apache\conf\httpd.conf` i upewnij się, że poniższa linia **nie** jest zakomentowana (brak `#` na początku):

```apache
LoadModule rewrite_module modules/mod_rewrite.so
```

Oraz że plik vhostów jest dołączany:

```apache
Include conf/extra/httpd-vhosts.conf
```

### 2b. Ustaw nasłuchiwanie na wszystkich interfejsach

W tym samym pliku `httpd.conf` znajdź linię `Listen` i upewnij się, że wygląda tak:

```apache
Listen 0.0.0.0:80
```

*(Dzięki temu Apache będzie dostępny nie tylko na `localhost`, ale i z sieci LAN)*

### 2c. Dodaj Virtual Host

Otwórz `C:\xampp\apache\conf\extra\httpd-vhosts.conf` i **dopisz na końcu**:

```apache
<VirtualHost *:80>
    DocumentRoot "G:/TubeYou/public"
    ServerName tubeyou.local

    <Directory "G:/TubeYou/public">
        Options FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog "G:/TubeYou/storage/logs/apache_error.log"
    CustomLog "G:/TubeYou/storage/logs/apache_access.log" combined
</VirtualHost>
```

> **Ważne:** ścieżki w Apache używają `/` (slash), **nie** `\` (backslash).  
> `DocumentRoot` wskazuje na `public/` — to jedyna ścieżka publicznie dostępna przez HTTP.

Zrestartuj Apache w XAMPP Control Panel (Stop → Start) po każdej zmianie konfiguracji.

---

## Krok 3 — Przygotowanie projektu na pendrivie

Projekt powinien znajdować się w `G:\TubeYou` (skopiowany lub sklonowany).

Utwórz wymagane katalogi:

```powershell
New-Item -ItemType Directory -Force "G:\TubeYou\storage\logs"
New-Item -ItemType Directory -Force "G:\TubeYou\storage\rate_limits"
New-Item -ItemType Directory -Force "G:\TubeYou\public\uploads"
```

Nadaj uprawnienia zapisu (Apache musi móc zapisywać pliki):

```powershell
icacls "G:\TubeYou\public\uploads" /grant "EVERYONE:(OI)(CI)F"
icacls "G:\TubeYou\storage"        /grant "EVERYONE:(OI)(CI)F"
```

---

## Krok 4 — Instalacja zależności PHP (Composer)

Otwórz terminal PowerShell w katalogu projektu:

```powershell
cd G:\TubeYou
composer install --no-dev
```

> Jeśli Composer nie jest zainstalowany: pobierz z https://getcomposer.org i zainstaluj globalnie.  
> Po instalacji otwórz nowy terminal (odśwież PATH).

---

## Krok 5 — Konfiguracja pliku .env

Skopiuj przykładowy plik konfiguracyjny:

```powershell
Copy-Item "G:\TubeYou\.env.example" "G:\TubeYou\.env"
```

Otwórz `G:\TubeYou\.env` w Notatniku i uzupełnij:

```env
# Tryb aplikacji: development = widać błędy PHP, production = błędy trafiają tylko do logów
APP_ENV=production

# Baza danych (XAMPP MySQL domyślnie: root bez hasła)
DB_HOST=localhost
DB_NAME=tubeyou
DB_USER=root
DB_PASSWORD=

# E-mail (Resend API lub SMTP)
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=twoj@gmail.com
MAIL_PASSWORD=twoje_haslo_aplikacji
MAIL_FROM_NAME=TubeYou
RESEND_API_KEY=twoj_klucz_resend

# ffmpeg — pełne ścieżki do plików .exe na dysku systemowym
FFMPEG_PATH=C:/ffmpeg/bin/ffmpeg.exe
FFPROBE_PATH=C:/ffmpeg/bin/ffprobe.exe
```

> Ustaw `APP_ENV=development` tylko gdy debugujesz — wtedy błędy PHP będą widoczne w przeglądarce.  
> Na `production` błędy trafiają wyłącznie do pliku `storage/logs/apache_error.log`.

---

## Krok 6 — Instalacja ffmpeg

1. Pobierz **ffmpeg release essentials** z https://www.gyan.dev/ffmpeg/builds/
2. Wypakuj zawartość tak, żeby struktura była:
   ```
   C:\ffmpeg\bin\ffmpeg.exe
   C:\ffmpeg\bin\ffprobe.exe
   ```
3. Zweryfikuj że działa:

```powershell
& "C:\ffmpeg\bin\ffmpeg.exe" -version
& "C:\ffmpeg\bin\ffprobe.exe" -version
```

Obie komendy powinny wypisać numer wersji.

---

## Krok 7 — Baza danych

### Opcja A — przez phpMyAdmin (graficznie)

1. Otwórz `http://localhost/phpmyadmin`
2. Kliknij **Nowa baza danych**, wpisz `tubeyou`, kliknij **Utwórz**
3. Przejdź do zakładki **SQL**
4. Wklej zawartość pliku `G:\TubeYou\database\schema.sql` i kliknij **Wykonaj**

### Opcja B — przez terminal

```powershell
& "C:\xampp\mysql\bin\mysql.exe" -u root -e "CREATE DATABASE IF NOT EXISTS tubeyou CHARACTER SET utf8mb4;"
& "C:\xampp\mysql\bin\mysql.exe" -u root tubeyou -e "source G:/TubeYou/database/schema.sql"
```

---

## Krok 8 — Zwiększ limity PHP dla wideo

Otwórz `C:\xampp\php\php.ini` i zmień (lub dodaj) wartości:

```ini
upload_max_filesize = 512M
post_max_size       = 512M
max_execution_time  = 300
memory_limit        = 256M
```

Zrestartuj Apache po każdej zmianie `php.ini`.

---

## Krok 9 — Dostęp z sieci LAN

### Sprawdź adres IP serwera

```powershell
ipconfig
```

Szukaj sekcji **Ethernet adapter** lub **Wireless LAN** i linii `IPv4 Address` — np. `192.168.1.100`.

### Otwórz port 80 w Zaporze Windows

```powershell
New-NetFirewallRule -DisplayName "Apache HTTP LAN" `
    -Direction Inbound -Protocol TCP -LocalPort 80 -Action Allow
```

### Połączenie z innych urządzeń

Inne komputery i telefony w tej samej sieci Wi-Fi/LAN wchodzą na:

```
http://192.168.1.100
```

*(zamień `192.168.1.100` na adres IP serwera)*

> Nie potrzeba żadnej konfiguracji na urządzeniu klienckim — wystarczy przeglądarka i ta sama sieć.

---

## Szybka lista kontrolna

```
[ ] XAMPP zainstalowany, Apache i MySQL uruchomione (zielone tło)
[ ] mod_rewrite włączony w httpd.conf
[ ] Listen 0.0.0.0:80 ustawione w httpd.conf
[ ] Virtual Host dodany w httpd-vhosts.conf (DocumentRoot → .../public)
[ ] Apache zrestartowany po zmianach konfiguracji
[ ] Foldery storage/logs i public/uploads utworzone z uprawnieniami (icacls)
[ ] composer install --no-dev wykonany w G:\TubeYou
[ ] .env uzupełniony (DB_HOST, DB_NAME, ffmpeg, APP_ENV)
[ ] Baza danych tubeyou utworzona, schema.sql zaimportowany
[ ] php.ini — limity upload zwiększone, Apache zrestartowany
[ ] Reguła zapory dla portu 80 dodana
[ ] IP serwera sprawdzone (ipconfig) i przekazane innym użytkownikom
```

---

## Troubleshooting

### Strona zwraca 403 Forbidden

**Przyczyna A:** Apache nie ma dostępu do katalogu.
- Sprawdź czy w `httpd-vhosts.conf` jest `Require all granted` i `AllowOverride All`
- Sprawdź czy ścieżka `DocumentRoot` używa `/` (slash), nie `\` (backslash)
- Sprawdź czy `DocumentRoot` wskazuje na `G:/TubeYou/public`, nie na `G:/TubeYou`

**Przyczyna B:** Pendrive nie jest podpięty lub zmieniła się litera dysku.
- Sprawdź w Eksploratorze plików, pod jaką literą widoczny jest pendrive
- Zaktualizuj ścieżkę w `httpd-vhosts.conf` i zrestartuj Apache

---

### Każda podstrona zwraca 404 (tylko strona główna działa)

**Przyczyna:** `mod_rewrite` nie działa lub `.htaccess` jest ignorowany.

1. Sprawdź w `httpd.conf`:
   ```apache
   LoadModule rewrite_module modules/mod_rewrite.so
   ```
2. Sprawdź czy `AllowOverride All` jest w bloku `<Directory>` Vhosta
3. Sprawdź czy plik `G:\TubeYou\public\.htaccess` istnieje

---

### Pusta biała strona lub błąd PHP

1. Ustaw w `.env` → `APP_ENV=development` — wtedy błąd pojawi się w przeglądarce
2. Sprawdź logi:
   - `G:\TubeYou\storage\logs\apache_error.log`
   - `C:\xampp\apache\logs\error.log`
3. Upewnij się że uruchomiono `composer install --no-dev`
4. Upewnij się że plik `.env` istnieje (nie tylko `.env.example`)

---

### Nie można połączyć z bazą danych

1. Sprawdź czy **MySQL** jest uruchomiony w XAMPP Control Panel
2. Sprawdź `.env`:
   ```env
   DB_HOST=localhost
   DB_NAME=tubeyou
   DB_USER=root
   DB_PASSWORD=
   ```
3. Sprawdź czy baza `tubeyou` istnieje w `http://localhost/phpmyadmin`
4. Jeśli hasło MySQL było zmieniane, wpisz je w `DB_PASSWORD`

---

### Inne komputery nie mogą się połączyć

Sprawdź kolejno:

1. **Apache nasłuchuje na 0.0.0.0** — w `httpd.conf`:
   ```apache
   Listen 0.0.0.0:80
   ```

2. **Zapora Windows** — sprawdź czy reguła istnieje:
   ```powershell
   Get-NetFirewallRule -DisplayName "Apache HTTP LAN"
   ```
   Jeśli nie ma, dodaj ją (Krok 9).

3. **Ta sama podsieć** — IP serwera i klienta muszą być w tej samej sieci, np. obydwa `192.168.1.x`.

4. **Test tymczasowy** — wyłącz zaporę (przywróć po teście!):
   ```powershell
   netsh advfirewall set allprofiles state off
   # po teście:
   netsh advfirewall set allprofiles state on
   ```
   Jeśli po wyłączeniu zadziała — problem jest z regułą zapory, nie z Apache.

5. **Izolacja klientów Wi-Fi** — niektóre routery/hotspoty mają włączoną opcję AP isolation (urządzenia Wi-Fi nie widzą się nawzajem). Sprawdź ustawienia routera lub podłącz kabel Ethernet.

---

### Wideo się nie uploaduje lub nie konwertuje

1. **Limity PHP** — sprawdź `C:\xampp\php\php.ini` (Krok 8), zrestartuj Apache po zmianie
2. **Uprawnienia** — sprawdź czy Apache może pisać do `public/uploads`:
   ```powershell
   icacls "G:\TubeYou\public\uploads"
   ```
   Kolumna `EVERYONE` powinna mieć `(OI)(CI)F`.
3. **ffmpeg** — sprawdź ścieżki w `.env` (muszą być pełne ścieżki z `.exe`):
   ```powershell
   & "C:\ffmpeg\bin\ffmpeg.exe" -version
   ```
4. **exec zablokowany** — sprawdź czy w `C:\xampp\php\php.ini` nie ma `disable_functions = exec,shell_exec,...`

---

### Pendrive zmienił literę dysku po przepięciu

Apache nie uruchomi się jeśli `DocumentRoot` wskazuje na nieistniejącą ścieżkę.

**Doraźnie:**
1. Otwórz Eksplorator plików → Ten komputer — sprawdź aktualną literę pendrive'a
2. Otwórz `C:\xampp\apache\conf\extra\httpd-vhosts.conf`
3. Zmień literę dysku w `DocumentRoot` i `<Directory>`
4. Zrestartuj Apache

**Na stałe (zalecane):**
1. Otwórz **Zarządzanie dyskami** (`diskmgmt.msc`)
2. Prawy klik na pendrive → **Zmień literę dysku i ścieżki**
3. Przypisz stałą literę (np. `G:`) — upewnij się że ta litera nie jest używana przez inne urządzenie
4. Od tej pory pendrive będzie miał zawsze tę samą literę na tym komputerze

---

### Strona ładuje się, ale CSS/JS nie działają

**Przyczyna:** `DocumentRoot` wskazuje na `G:/TubeYou` zamiast `G:/TubeYou/public`.  
Zasoby statyczne (CSS, JS, obrazki) muszą być serwowane z katalogu `public/`.

Sprawdź w `httpd-vhosts.conf`:
```apache
DocumentRoot "G:/TubeYou/public"
```

---

### Composer: "No such file or directory" lub "Permission denied"

1. Upewnij się że Composer jest zainstalowany globalnie (dostępny jako `composer` w terminalu)
2. Uruchom terminal jako **Administrator**
3. Upewnij się że jesteś w katalogu projektu:
   ```powershell
   cd G:\TubeYou
   composer install --no-dev
   ```
4. Jeśli błąd dot. `vendor/autoload.php` — usuń folder `vendor/` i uruchom ponownie

---

### phpMyAdmin zwraca "Access denied"

Domyślnie XAMPP MySQL: użytkownik `root`, bez hasła. Jeśli ustawiono hasło:

```powershell
& "C:\xampp\mysql\bin\mysql.exe" -u root -p
```

Podaj hasło, następnie:
```sql
CREATE DATABASE IF NOT EXISTS tubeyou CHARACTER SET utf8mb4;
USE tubeyou;
SOURCE G:/TubeYou/database/schema.sql;
```

---

## Struktura katalogów na pendrivie

```
G:\TubeYou\
├── public\              ← DocumentRoot Apache (jedyna ścieżka przez HTTP)
│   ├── index.php        ← punkt wejścia
│   ├── uploads\         ← wideo, miniatury, avatary (Apache musi mieć zapis)
│   └── .htaccess        ← rewrite rules
├── controllers\
├── models\
├── views\
├── helpers\
├── services\
├── config\
├── database\
│   └── schema.sql       ← schemat bazy danych
├── storage\
│   ├── logs\            ← logi Apache i błędy PHP (Apache musi mieć zapis)
│   └── rate_limits\     ← pliki rate limitera (Apache musi mieć zapis)
├── vendor\              ← zależności Composer (generowane przez composer install)
├── .env                 ← konfiguracja (NIE commitować!)
└── .env.example         ← szablon konfiguracji
```

---

## Uwagi bezpieczeństwa

- Plik `.env` zawiera hasła — **nigdy nie wgrywaj go do publicznego repozytorium**
- W trybie produkcyjnym ustaw `APP_ENV=production` — ukrywa błędy PHP przed użytkownikiem
- Hosting LAN jest przeznaczony do użytku w zaufanej sieci lokalnej, nie jako publiczny serwer internetowy
- Katalog `storage/` i pliki konfiguracyjne nie są dostępne przez HTTP (Apache serwuje tylko `public/`)
