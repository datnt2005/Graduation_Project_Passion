<!DOCTYPE html>
<html>
<head>
    <title>Test Form</title>
    <meta charset="utf-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        body { font-family: Arial; padding: 20px; }
        label { display: block; margin-top: 10px; }
        input, select, textarea { padding: 5px; width: 300px; }
        button { margin-top: 15px; padding: 10px 15px; }
    </style>
</head>
<body>
    <h1>Test Form</h1>

    @if (session('success'))
        <div style="color: green">{{ session('success') }}</div>
    @endif

    <form action="{{ route('test-form.submit') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <label for="name">Name:</label>
        <input type="text" name="name" id="name" required>

        <label for="email">Email:</label>
        <input type="email" name="email" id="email" required>

        <label for="file">Upload File:</label>
        <input type="file" name="file" id="file">

        <button type="submit">Submit</button>
    </form>
</body>
</html>
