<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Attendance Scan</title>
    <style>
      body {
        margin: 0;
        font-family: Arial, Helvetica, sans-serif;
        background: #0f172a;
        color: #0f172a;
      }
      .wrap {
        min-height: 100vh;
        display: grid;
        place-items: center;
        padding: 24px;
      }
      .card {
        width: 100%;
        max-width: 420px;
        background: #ffffff;
        border-radius: 16px;
        padding: 24px;
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
        text-align: center;
      }
      .title {
        margin: 0 0 8px;
        font-size: 20px;
        font-weight: 700;
      }
      .message {
        margin: 0 0 16px;
        color: #475569;
        font-size: 14px;
      }
      .btn {
        display: inline-block;
        padding: 10px 16px;
        border-radius: 10px;
        background: #0f172a;
        color: #ffffff;
        text-decoration: none;
        font-size: 14px;
      }
    </style>
  </head>
  <body>
    <div class="wrap">
      <div class="card">
        <h1 class="title">{{ $title }}</h1>
        <p class="message">{{ $message }}</p>
        <a class="btn" href="/employee/attendance">Back to Attendance</a>
      </div>
    </div>
  </body>
</html>
