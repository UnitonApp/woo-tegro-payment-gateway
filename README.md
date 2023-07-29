# Tegro Payment Gateway для WooCommerce

Tegro Payment Gateway - это платежный шлюз, который позволяет принимать платежи через Tegro в WooCommerce.

## Установка

1. Скачайте папку `tegro-payment-gateway` из репозитория.
2. Загрузите папку `tegro-payment-gateway` в папку `wp-content/plugins/` на вашем сервере.
3. Активируйте плагин Tegro Payment Gateway в разделе "Плагины" в административной панели WordPress.
4. Перейдите на страницу "Настройки" -> "Оплата" в WooCommerce, выберите Tegro Payment Gateway и настройте его.

## Настройки

- `enabled`: определяет, включен ли платежный шлюз Tegro или нет;
- `title`: определяет заголовок для платежного шлюза Tegro;
- `description`: определяет описание для платежного шлюза Tegro;
- `api_create_order_url`: определяет URL для создания заказа через API;
- `api_check_order_url`: определяет URL для проверки заказа через API;
- `shop_id`: определяет публичный ключ вашего магазина в Tegro;
- `api_key`: определяет API ключ, используемый для подписи информации о платеже;
- `email`: определяет адрес электронной почты, на который будут отправляться уведомления о платежах;
- `test_mode`: определяет, включен ли тестовый режим для платежного шлюза Tegro.

## Совместимость

Tegro Payment Gateway совместим с WooCommerce версии 3.0 и выше.

## Лицензия

Tegro Payment Gateway распространяется по лицензии GPL версии 2 или выше.
