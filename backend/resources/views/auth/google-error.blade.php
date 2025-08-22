<!DOCTYPE html>
<html>
<head>
    <title>Google Login Error</title>
</head>
<body>
    <script>
        window.opener.postMessage({
            error: "{{ $message }}"
        }, "{{ env('FRONTEND_URL', 'https://passionfpt.shop/') }}");
        setTimeout(() => window.close(), 500); // Đóng popup sau 500ms
    </script>
</body>
</html>
