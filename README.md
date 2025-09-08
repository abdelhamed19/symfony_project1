# Symfony REST API Project

This is a robust RESTful API built with the Symfony framework. It provides full CRUD (Create, Read, Update, Delete) functionality for `Category` and `Article` entities, featuring JWT authentication, file uploads, pagination, and advanced serialization.

## Table of Contents

1.  [Core Technologies](#core-technologies)
2.  [Features](#features)
3.  [API Endpoints](#api-endpoints)
    *   [Authentication](#authentication)
    *   [Categories](#categories)
    *   [Articles](#articles)
4.  [Installation](#installation)
5.  [Configuration](#configuration)
6.  [Architectural Notes](#architectural-notes)

## Core Technologies

This project leverages a powerful stack of Symfony bundles and libraries to achieve its functionality:

*   **Symfony 7+**: The core framework for building the application.
*   **Doctrine ORM**: For database abstraction and entity management.
*   **FOSRestBundle**: To rapidly create RESTful controllers and view layers.
*   **JMS Serializer Bundle**: For advanced object serialization, including serialization groups (`category:read`, `article:read`).
*   **LexikJWTAuthenticationBundle**: For securing API endpoints using JSON Web Tokens (JWT).
*   **KnpPaginatorBundle**: For easy and flexible pagination of resource collections.

## Features

*   **JWT Authentication**: Secure, stateless authentication for accessing protected endpoints.
*   **Full CRUD for Categories**: Create, read, update, and delete categories.
*   **Full CRUD for Articles**: Create, read, update, and delete articles, with a relationship to a category.
*   **Reusable File Uploads**: A custom `FileTrait` (`src/Trait/FileTrait.php`) provides a clean, reusable mechanism for handling file uploads and deletions across different entities.
*   **Manual Sorting**: Categories include a `sort_order` field, with dedicated endpoints to manage the display order.
*   **Pagination**: The `list` endpoints for both articles and categories are paginated.
*   **Advanced Validation**: Symfony Forms (`ArticleType`, `CategoryType`, `UpdateSortOrderType`) are used on the backend to validate incoming API data.
*   **Timestampable Behavior**: `created_at` and `updated_at` fields are automatically managed for all entities.

## API Endpoints

The base URL for all endpoints is `/api`.

### Authentication

#### 1. Get JWT Token

*   **Endpoint**: `POST /api/login_check`
*   **Description**: Authenticates a user and returns a JWT token for accessing protected routes.
*   **Body (JSON)**:
    ```json
    {
        "username": "your_username",
        "password": "your_password"
    }
    ```
*   **Success Response (200 OK)**:
    ```json
    {
        "token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9..."
    }
    ```

---

### Categories

*   **Entity**: `Category (id, name, file, sort_order, created_at, updated_at)`
*   **Authentication**: Required for all endpoints.

| Action                      | Method | Endpoint                             | Description                                                        | Request Body                                     |
| :-------------------------- | :----- | :----------------------------------- | :----------------------------------------------------------------- | :----------------------------------------------- |
| **List Categories**         | `GET`    | `/api/categories?page=1&limit=10`    | Retrieves a paginated list of categories, ordered by `sort_order`. | -                                                |
| **Get Single Category**     | `GET`    | `/api/categories/{id}`               | Retrieves a single category by its ID.                             | -                                                |
| **Create Category**         | `POST`   | `/api/categories`                    | Creates a new category. Use `form-data` for file uploads.          | `form-data`: `name` (string), `imageFile` (file) |
| **Update Category**         | `POST`   | `/api/categories/{id}`               | Updates a category's name and/or image. Use `form-data`.           | `form-data`: `name` (string), `imageFile` (file) |
| **Delete Category**         | `DELETE` | `/api/categories/{id}`               | Deletes a category and its associated image file.                  | -                                                |
| **Update Sort Order (Batch)** | `POST`   | `/api/categories/update-order`       | Updates the `sort_order` for multiple categories at once.          | `JSON`: `[{ "id": 1, "sort_order": 3 }, ...]`     |
| **Update Sort Order (Single)**| `PATCH`  | `/api/categories/{id}/sort-order`    | Updates the `sort_order` for a single category.                    | `JSON`: `{ "sort_order": 5 }`                    |

---

### Articles

*   **Entity**: `Article (id, title, category, created_at, updated_at)`
*   **Authentication**: Required for all endpoints.

| Action             | Method | Endpoint                     | Description                                  | Request Body                                         |
| :----------------- | :----- | :--------------------------- | :------------------------------------------- | :--------------------------------------------------- |
| **List Articles**  | `GET`    | `/api/articles?page=1&limit=10` | Retrieves a paginated list of articles.      | -                                                    |
| **Get Single Article** | `GET`    | `/api/articles/{id}`         | Retrieves a single article by its ID.        | -                                                    |
| **Create Article** | `POST`   | `/api/articles`              | Creates a new article.                       | `JSON`: `{ "title": "New Title", "category": 1 }` (category ID) |
| **Update Article** | `PUT`    | `/api/articles/{id}`         | Updates an article's title and/or category.  | `JSON`: `{ "title": "Updated Title", "category": 2 }` |
| **Delete Article** | `DELETE` | `/api/articles/{id}`         | Deletes an article.                          | -                                                    |

## Installation

1.  **Clone the repository:**
    ```bash
    git clone https://https://github.com/abdelhamed19/symfony_project1.git
    cd project
    ```

2.  **Install PHP dependencies:**
    ```bash
    composer install
    ```

3.  **Configure your environment:**
    Create a `.env.local` file and configure your `DATABASE_URL`:
    ```env
    # .env.local
    DATABASE_URL="mysql://db_user:db_password@127.0.0.1:3306/db_name?serverVersion=mariadb-10.4.21"
    ```

4.  **Generate JWT keys:**
    ```bash
    php bin/console lexik:jwt:generate-keypair
    ```

5.  **Set up the database:**
    ```bash
    # Create the database
    php bin/console doctrine:database:create

    # Run database migrations
    php bin/console doctrine:migrations:migrate
    ```

6.  **Start the local server:**
    ```bash
    symfony server:start
    ```
    The API will be available at `http://127.0.0.1:8000`.

## Architectural Notes

*   **Service Layer**: Business logic (e.g., setting `sort_order` on new categories ) is handled in Service classes (`src/Service/`) to keep controllers thin.
*   **Validation**: Validation is primarily handled by Symfony Forms, which read constraints directly from entity attributes. For simple, one-off validation, the `ValidatorInterface` is used directly in the controller.
*   **FOSRestBundle vs. Modern Symfony**: While this project uses `FOSRestBundle`, it's important to note that modern Symfony (5.x+) provides most of this functionality out-of-the-box. The controllers are written to be compatible with both approaches, primarily returning `JsonResponse` objects via `$this->json()`, which is the modern, recommended practice.
