<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <base target="_top">

    <title>Redirecting...</title>

    {{-- we need this view because i have to leave the iframe --}}
    <script type="text/javascript">
        window.top.location.href = "{!! $url !!}";
    </script>
</head>

<body>
</body>

</html>