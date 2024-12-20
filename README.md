## О фреймворке Sympledynamic

Лёгкий и гибкий фреймворк, написанный на PHP

## Конфигурация
Версия PHP: 8.4

Необходимые расширения PHP: 
- simplexml 
- yaml 
- mbstring

## Интеграция

- laravel/database 8.83
- doctrine/mongodb-odm 2.7
- twig/twig 3.14
- guzzlehttp/guzzle 7.9

## Реализация

### Модель
#### Результат запроса | Model 
Содержит необходимую информацию из базы данных
#### Исполнитель запроса | Repository
Выполняет действия для получения результата
#### Создатель запроса | Builder
Создаёт исполнителя, реализуя интерфейс

### Представление
#### Макет ответа | View
Создаёт независимую структуру ответа
#### Отправитель ответа | Service
Передаёт ответ из подпрограммы в другую программу
#### Преобразователь | Resource
Использует полиморфный вариант ответа

### Контроллер
#### Обработчик | Controller
Использует методы исполнителя запроса
#### Получатель | Route
Получает запрос от другой программы
#### Посредник | Middleware
Передаёт данные от получателя к обработчику
