<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giriş Yap</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        body {
            background: linear-gradient(to right, #0f2027, #2c5364);
            color: #fff;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            font-family: 'Arial', sans-serif;
        }
        .card {
            background-color: rgba(255, 255, 255, 0.9);
            border-radius: 15px;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.3);
        }
        .btn-success {
            background-color: #2c5364;
            border: none;
            transition: background-color 0.3s ease;
        }
        .btn-success:hover {
            background-color: #0f2027;
        }
        .alert-custom {
            margin-top: 10px;
            color: #721c24;
            background-color: #f8d7da;
            border-color: #f5c6cb;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-4">
                <div class="card p-4">
                    <div class="card-body">
                        <h3 class="text-center mb-4 text-dark">Giriş Yap</h3>
                        <form id="loginForm">
                            <div class="mb-3">
                                <label for="email" class="form-label">E-posta</label>
                                <input type="email" class="form-control" id="email" placeholder="E-posta adresinizi girin" required>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Şifre</label>
                                <input type="password" class="form-control" id="password" placeholder="Şifrenizi girin" required>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                
                            </div>
                            <button type="submit" class="btn btn-success w-100">Giriş Yap</button>
                        </form>
                        <!-- Şifremi Unuttum Mesajı -->
                        <div id="alertPlaceholder"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
     $(document).ready(function () {
    $('#loginForm').on('submit', function (event) {
        event.preventDefault();

        const email = $('#email').val();
        const password = $('#password').val();

        $.ajax({
            type: 'POST',
            url: '../backend/login.php',
            data: { email: email, password: password },
            dataType: 'json',
            success: function (response) {
                if (response.success) {
                    window.location.href = 'index.php'; // Başarılı giriş sonrası yönlendirme
                } else {
                    $('#alertPlaceholder').html(`
                        <div class="alert alert-danger" role="alert">
                            ${response.message}
                        </div>
                    `);
                }
            },
            error: function (xhr, status, error) {
                $('#alertPlaceholder').html(`
                    <div class="alert alert-danger" role="alert">
                        AJAX Hatası: ${xhr.responseText}
                    </div>
                `);
            }
        });
    });
});

    </script>
</body>
</html>
