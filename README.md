# Task App Backend

## Setup

1.  **Clone & Install**

    ```bash
    git clone https://github.com/prafful-panwar/task-app-prafful.git
    cd task-app-prafful
    composer install
    cp .env.example .env
    php artisan key:generate
    ```

2.  **Configuration**
    Update your `.env` file to match your environment:

    ```ini
    APP_NAME="Task App"
    DB_CONNECTION=mysql
    DB_HOST=127.0.0.1
    DB_PORT=3306
    DB_DATABASE=task_app
    DB_USERNAME=root
    DB_PASSWORD=
    ```

3.  **Database & Seeding**
    Run migrations and (optionally) seed the database:

    ```bash
    php artisan migrate
    php artisan db:seed
    ```

4.  **Start Server**
    Start the local development server:

    ```bash
    php artisan serve
    ```

    The API will be available at: `http://127.0.0.1:8000/api`

    _(Or if using Laravel Herd: `http://task-app-prafful.test/api`)_

## API Documentation

### Endpoints

| Method   | Endpoint      | Description                                      |
| :------- | :------------ | :----------------------------------------------- |
| `GET`    | `/tasks`      | List all tasks (supports pagination & filtering) |
| `POST`   | `/tasks`      | Create a new task                                |
| `GET`    | `/tasks/{id}` | Show a specific task                             |
| `PUT`    | `/tasks/{id}` | Update a task (full or partial)                  |
| `DELETE` | `/tasks/{id}` | Delete a task                                    |

### Examples

#### 1. List Tasks

**GET** `/tasks?status=pending&per_page=5`

#### 2. Get Task

**GET** `/tasks/1`

#### 3. Create Task

**POST** `/tasks`

```json
{
    "title": "Fix Mobile API",
    "description": "Add human readable dates",
    "status": "in_progress",
    "due_date": "2024-01-20"
}
```

#### 4. Update Task

**PUT** `/tasks/1`

```json
{
    "status": "completed"
}
```

#### 5. Delete Task

**DELETE** `/tasks/{id}`
