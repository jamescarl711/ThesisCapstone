<template>
  <div class="min-h-screen flex flex-col items-center justify-center bg-gray-100">
    <h1 class="text-4xl font-bold mb-6">Thesis Capstone System</h1>

    <div class="flex gap-4">
      <a href="/login" class="bg-blue-500 text-white px-6 py-3 rounded hover:bg-blue-600">
        Login
      </a>
      <a href="/register" class="bg-green-500 text-white px-6 py-3 rounded hover:bg-green-600">
        Register
      </a>
    </div>

    <div class="mt-6 text-gray-600">
      Or quick login for testing:
    </div>

    <div class="flex gap-4 mt-2">
      <form action="/login" method="POST">
        @csrf
        <input type="hidden" name="email" value="admin@example.com" />
        <input type="hidden" name="password" value="password" />
        <button type="submit" class="bg-red-500 text-white px-6 py-2 rounded hover:bg-red-600">
          Login as Admin
        </button>
      </form>

      <form action="/login" method="POST">
        @csrf
        <input type="hidden" name="email" value="user@example.com" />
        <input type="hidden" name="password" value="password" />
        <button type="submit" class="bg-yellow-500 text-white px-6 py-2 rounded hover:bg-yellow-600">
          Login as User
        </button>
      </form>
    </div>
  </div>
</template>
