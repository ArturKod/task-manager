# Task Manager API

[![CI Status](https://github.com/your-username/task-manager/actions/workflows/ci.yml/badge.svg)](https://github.com/your-username/task-manager/actions/workflows/ci.yml)

Сервис управления задачами с событиями, очередями и аудитом.

## Требования

- Docker
- Docker Compose
- Git

## Быстрый старт

```bash
# Клонирование репозитория
git clone https://github.com/your-username/task-manager.git
cd task-manager

# Копирование .env
cp src/.env.example src/.env

# Запуск контейнеров
docker-compose up -d

# Установка зависимостей
docker-compose exec app composer install

# Генерация ключа
docker-compose exec app php artisan key:generate

# Миграции
docker-compose exec app php artisan migrate

# Запуск тестов
docker-compose exec app php artisan test