# Instrukcja hostowania TubeYou w sieci LAN

> Projekt hostowany z pendrive'a — ścieżki używają litery dysku `E:\`
> (zmień na właściwą literę swojego pendrive'a, np. `D:\`, `F:\` itp.)

---

## Wymagania

| Składnik | Wersja | Link |
|----------|--------|------|
| XAMPP | najnowszy (PHP 8.1+) | https://www.apachefriends.org |
| ffmpeg | release essentials | https://www.gyan.dev/ffmpeg/builds |
| Composer | najnowszy | https://getcomposer.org |

---

## Krok 1 — Instalacja XAMPP

1. Pobierz i zainstaluj XAMPP w `C:\xampp`
2. Uruchom **XAMPP Control Panel** jako Administrator
3. Kliknij **Start** przy **Apache** i **MySQL**

> XAMPP instalujemy na dysku systemowym `C:\`, **nie** na pendrivie.

---

## Krok 2 — Konfiguracja Apache

### 2a. Włącz mod_rewrite

Otwórz `C:\xampp\apache\conf\httpd.conf` i upewnij się, że ta linia **nie** jest zakomentowana:

```apache
LoadModule rewrite_module modules/mod_rewrite.so
```

Oraz że plik vhosts jest dołączany (odkomentuj jeśli zaczyna się od `#`):

```apache
Include conf/extra/httpd-vhosts.conf
```

### 2b. Dodaj Virtual Host

Otwórz `C:\xampp\apache\conf\extra\httpd-vhosts.conf` i dopisz na końcu:

```apache
<VirtualHost *:80>
    DocumentRoot "E:/TubeYou"
    ServerName tubeyou.local

    <Directory "E:/TubeYou">
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog "E:/TubeYou/storage/logs/apache_error.log"
    CustomLog "E:/TubeYou/storage/logs/apache_access.log" combined
</VirtualHost>
```

> Zwróć uwagę: ścieżki używają `/` (slash), nie `\` (backslash) — wymóg Apache.

Zrestartuj Apache w XAMPP Control Panel po każdej zmianie konfiguracji.

---

## Krok 3 — Przygotowanie folderów

Utwórz wymagane katalogi (jeśli jeszcze nie istnieją):

```powershell
New-Item -ItemType Directory -Force "E:\TubeYou\storage\logs"
New-Item -ItemType Directory -Force "E:\TubeYou\public\uploads"
```

Nadaj uprawnienia zapisu dla Apache:

```powershell
icacls "E:\TubeYou\public\uploads" /grant "EVERYONE:(OI)(CI)F"
icacls "E:\TubeYou\storage" /grant "EVERYONE:(OI)(CI)F"
```

---

## Krok 4 — Instalacja zależności PHP

Otwórz terminal w katalogu projektu i uruchom:

```powershell
cd E:\TubeYou
composer install --no-dev
```

> Jeśli Composer nie jest zainstalowany, pobierz go z https://getcomposer.org i zainstaluj globalnie.

---

## Krok 5 — Konfiguracja pliku .env

Skopiuj `.env.example` jako `.env` w katalogu projektu:

```powershell
Copy-Item "E:\TubeYou\.env.example" "E:\TubeYou\.env"
```

Otwórz `.env` i uzupełnij wartości:

```env
DB_HOST=localhost
DB_NAME=tubeyou
DB_USER=root
DB_PASSWORD=

MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=twoj@gmail.com
MAIL_PASSWORD=twoje_haslo_aplikacji
MAIL_FROM_NAME=TubeYou

FFMPEG_PATH=C:/ffmpeg/bin/ffmpeg.exe
FFPROBE_PATH=C:/ffmpeg/bin/ffprobe.exe

RESEND_API_KEY=twoj_klucz_resend
```

> ffmpeg instalujemy na `C:\ffmpeg`, nie na pendrivie (pendrive jest wolny, ffmpeg przetwarza duże pliki).

---

## Krok 6 — Instalacja ffmpeg

1. Pobierz **ffmpeg release essentials** z https://www.gyan.dev/ffmpeg/builds
2. Wypakuj do `C:\ffmpeg` (struktura: `C:\ffmpeg\bin\ffmpeg.exe`)
3. Sprawdź że działa:

```powershell
& "C:\ffmpeg\bin\ffmpeg.exe" -version
```

---

## Krok 7 — Baza danych

Otwórz phpMyAdmin pod adresem `http://localhost/phpmyadmin`, następnie:

1. Zakładka **SQL**
2. Wklej zawartość pliku `database/schema.sql` i kliknij **Wykonaj**

Lub przez terminal:

```powershell
& "C:\xampp\mysql\bin\mysql.exe" -u root -e "source E:/TubeYou/database/schema.sql"
```

---

## Krok 8 — Zwiększ limity PHP dla wideo

Otwórz `C:\xampp\php\php.ini` i zmień (lub dodaj) poniższe wartości:

```ini
upload_max_filesize = 512M
post_max_size = 512M
max_execution_time = 300
memory_limit = 256M
```

Zrestartuj Apache po zapisaniu zmian.

---

## Krok 9 — Dostęp z sieci LAN

### Sprawdź IP serwera

```powershell
ipconfig
```

Szukaj linii `IPv4 Address` — np. `192.168.1.100`

### Otwórz port 80 w Zaporze Windows

```powershell
New-NetFirewallRule -DisplayName "Apache HTTP LAN" -Direction Inbound -Protocol TCP -LocalPort 80 -Action Allow
```

### Połączenie z innych urządzeń

Inne komputery i telefony w tej samej sieci Wi-Fi/LAN wchodzą na:

```
http://192.168.1.100
```

*(zamień na swój adres IP)*

---

## Troubleshooting

### Strona zwraca 403 Forbidden

- Sprawdź czy w `httpd-vhosts.conf` jest `Require all granted` i `AllowOverride All`
- Sprawdź czy ścieżka `DocumentRoot` używa `/` zamiast `\`
- Sprawdź czy pendrive jest podpięty i litera dysku się zgadza

---

### Każda podstrona zwraca 404

- Sprawdź czy `mod_rewrite` jest włączony w `httpd.conf`
- Sprawdź czy `AllowOverride All` jest ustawione (nie `None`)
- Sprawdź czy plik `.htaccess` istnieje w katalogu projektu

---

### Pusta strona lub błąd PHP

- Sprawdź logi: `E:\TubeYou\storage\logs\apache_error.log`
- Sprawdź też: `C:\xampp\apache\logs\error.log`
- Upewnij się że uruchomiono `composer install`

---

### Nie można połączyć z bazą danych

- Sprawdź czy MySQL jest uruchomiony w XAMPP Control Panel
- Sprawdź dane w `.env` — szczególnie `DB_HOST=localhost`
- Sprawdź czy baza `tubeyou` istnieje w phpMyAdmin

---

### Inne komputery nie mogą się połączyć

1. Sprawdź czy Apache nasłuchuje na wszystkich interfejsach — w `C:\xampp\apache\conf\httpd.conf`:
   ```apache
   Listen 0.0.0.0:80
   ```
2. Sprawdź regułę zapory (Krok 9)
3. Upewnij się że oba urządzenia są w tej samej podsieci (`192.168.x.x`)
4. Test tymczasowy — wyłącz zaporę:
   ```powershell
   netsh advfirewall set allprofiles state off
   ```
   Jeśli zadziała — problem jest z regułą zapory, nie z Apache.

---

### Wideo nie uploaduje się / nie konwertuje

- Sprawdź limity PHP (Krok 8)
- Sprawdź uprawnienia do folderu `uploads` (Krok 3)
- Sprawdź ścieżki ffmpeg w `.env` — muszą być pełne ścieżki z `.exe`
- Test ffmpeg: `& "C:\ffmpeg\bin\ffmpeg.exe" -version`

---

### Pendrive zmienił literę dysku po przepięciu

Apache nie uruchomi się jeśli `DocumentRoot` wskazuje na nieistniejącą ścieżkę.

1. Sprawdź aktualną literę: otwórz Eksplorator plików → Ten komputer
2. Zaktualizuj ścieżkę w `C:\xampp\apache\conf\extra\httpd-vhosts.conf`
3. Zrestartuj Apache

> Aby pendrive zawsze miał tę samą literę: Zarządzanie dyskami → prawy klik na pendrive → Zmień literę dysku → przypisz stałą literę (np. `E:`).

---

## Szybka lista kontrolna (checklist)

```
[ ] XAMPP zainstalowany, Apache i MySQL uruchomione
[ ] mod_rewrite włączony w httpd.conf
[ ] Virtual Host dodany w httpd-vhosts.conf
[ ] Foldery storage/logs i public/uploads utworzone z uprawnieniami
[ ] composer install wykonany
[ ] .env uzupełniony (baza, mail, ffmpeg)
[ ] Schemat bazy danych zaimportowany (schema.sql)
[ ] Limity PHP zwiększone w php.ini
[ ] Reguła zapory dla portu 80 dodana
[ ] IP serwera sprawdzone i przekazane innym użytkownikom
```
