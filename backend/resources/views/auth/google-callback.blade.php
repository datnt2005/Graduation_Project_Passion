<!DOCTYPE html>
<html>
<head>
    <title>Google Login Callback</title>
</head>
<body>
    <script>
        window.opener.postMessage({
            token: "{{ $token }}",
            user: @json($user)
        }, "{{ env('FRONTEND_URL', 'https://passionfpt.shop') }}");
        setTimeout(() => window.close(), 500); // Đóng popup sau 500ms
    </script>
</body>
</html>
