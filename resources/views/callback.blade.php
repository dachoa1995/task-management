<html>
<head>
    <meta charset="utf-8">
    <title>Callback</title>
    <script>
      window.opener.postMessage(
        {
          api_token: "{{ $api_token }}",
          name: "{{ $name }}"
        },
        "http://local.task.com/login/");
      window.close();
    </script>
</head>
<body>
</body>
</html>
