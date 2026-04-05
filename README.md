# Phisica — AI-тьютор по физике (PHP + SQLite)

Полностью готовый сайт без фреймворков: PHP 8+, SQLite, нативный JS и CSS.

## Как запустить

1. Загрузите файлы на хостинг/сервер с PHP 8+ и поддержкой SQLite.
2. Откройте `init_db.php` в браузере или запустите командой:
   ```bash
   php init_db.php
   ```
3. Откройте `.env` и вставьте ключ:
   ```
   XAI_API_KEY=ваш_ключ_grok
   ```
4. Перейдите на `register.php`, создайте пользователя, затем войдите через `login.php`.
5. После входа откройте `index.php`, выберите раздел и начните чат.

## Что реализовано

- Регистрация и вход с `password_hash()`/`password_verify()`.
- Проверка авторизации на защищённых страницах.
- Разделы физики на карточках.
- Страница темы с переходом в чат.
- Чат с сохранением истории в SQLite.
- Новый чат создаёт отдельную `chat_sessions` запись.
- API-интеграция с Grok (`https://api.x.ai/v1/chat/completions`) и системным промптом тьютора.

## Структура

- `index.php`, `login.php`, `register.php`, `logout.php`, `section.php`, `chat.php`
- `api/chat.php`
- `css/style.css`
- `js/chat.js`
- `init_db.php`
- `.env`, `.htaccess`
- `includes/*.php` (вспомогательные модули)

