# UserController API

Список эндпоинтов API в `UserController` для управления пользователями.

## Эндпоинты

### Регистрация нового пользователя

- **URL**: `/api/users/register`
- **Метод**: `POST`
- **Описание**: Позволяет новому пользователю зарегистрироваться, предоставив электронную почту и пароль.
- **Тело запроса**:
  ```json
  {
    "email": "user@example.com",
    "password": "strongpassword"
  }
  ```
- **Ответы**:
  - **201 Created**: Пользователь успешно создан.
    ```json
    {
      "id": 1,
      "email": "user@example.com"
    }
    ```
  - **400 Bad Request**: Неверные данные.
    ```json
    {
      "error": "Invalid data"
    }
    ```
  - **409 Conflict**: Пользователь уже существует.
    ```json
    {
      "error": "User already exists."
    }
    ```

### Обновление существующего пользователя

- **URL**: `/api/users/{id}`
- **Метод**: `PUT`
- **Описание**: Обновляет данные пользователя, предоставляя ID пользователя, электронную почту и/или пароль.
- **Тело запроса**:
  ```json
  {
    "email": "newuser@example.com",
    "password": "newstrongpassword"
  }
  ```
- **Ответы**:
  - **200 OK**: Пользователь успешно обновлен.
    ```json
    {
      "message": "User updated successfully"
    }
    ```
  - **400 Bad Request**: Неверные данные.
    ```json
    {
      "error": "Invalid email format."
    }
    ```
  - **404 Not Found**: Пользователь не найден.
    ```json
    {
      "error": "User not found"
    }
    ```

### Удаление существующего пользователя

- **URL**: `/api/users/{id}`
- **Метод**: `DELETE`
- **Описание**: Удаляет пользователя, предоставляя его ID.
- **Ответы**:
  - **204 No Content**: Пользователь успешно удален.
    ```json
    {
      "message": "User deleted successfully"
    }
    ```
  - **404 Not Found**: Пользователь не найден.
    ```json
    {
      "error": "User not found"
    }
    ```

### Вход пользователя

- **URL**: `/api/users/login`
- **Метод**: `POST`
- **Описание**: Позволяет пользователю войти в систему, предоставив электронную почту и пароль.
- **Тело запроса**:
  ```json
  {
    "email": "user@example.com",
    "password": "strongpassword"
  }
  ```
- **Ответы**:
  - **200 OK**: Пользователь успешно вошел в систему.
    ```json
    {
      "message": "User logged in successfully"
    }
    ```
  - **400 Bad Request**: Неверные учетные данные.
    ```json
    {
      "error": "Invalid credentials"
    }
    ```

### Получение пользователя по ID

- **URL**: `/api/users/{id}`
- **Метод**: `GET`
- **Описание**: Извлекает данные пользователя, предоставляя его ID.
- **Ответы**:
  - **200 OK**: Пользователь найден.
    ```json
    {
      "id": 1,
      "email": "user@example.com"
    }
    ```
  - **404 Not Found**: Пользователь не найден.
    ```json
    {
      "error": "User not found"
    }
    ```

## Swagger
Также для каждого метода апи были добавлены swagger атрибуты
![image](https://github.com/user-attachments/assets/4958d35c-207b-4124-8e6a-ffaf0373ef19)

И добавлен маршрут **/api/doc**, по которому открывается сгенерированная документация апи
![image](https://github.com/user-attachments/assets/0869545f-d692-4d8d-a094-eca9da60e8ac)

