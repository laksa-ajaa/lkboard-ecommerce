@extends('layouts.auth', ['title' => 'Register'])

@section('content')
    <x-auth-layout title="Create your account">

        <div class="w-full max-w-sm bg-white rounded-2xl border border-slate-200/90 p-8 shadow-md shadow-slate-900/10">

            <div class="flex items-center justify-center gap-2 mb-4">
                <img src="{{ asset('assets/img/logo.jpg') }}" alt="LKBoard Logo"
                    class="h-8 w-8 rounded-md object-cover shadow-sm shadow-indigo-500/40">
                <div class="flex flex-col">
                    <span class="text-sm font-semibold tracking-tight text-slate-900">
                        LKBoard
                    </span>
                    <span class="text-[10px] uppercase tracking-[0.18em] text-slate-400">
                        Mechanical Keyboards
                    </span>
                </div>
            </div>

            <h1 class="text-xl font-bold text-center">Create your account</h1>
            <p class="text-sm text-center text-slate-500 mt-1">Sign up to continue</p>

            <form action="{{ route('register.store') }}" method="POST" class="mt-6 space-y-4">
                @csrf

                <div>
                    <label class="text-sm font-medium text-slate-700">Name</label>
                    <input type="text" name="name"
                        class="mt-1 w-full border border-slate-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-indigo-500">
                </div>

                <div>
                    <label class="text-sm font-medium text-slate-700">Email</label>
                    <input type="email" name="email"
                        class="mt-1 w-full border border-slate-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-indigo-500">
                </div>

                <div>
                    <label class="text-sm font-medium text-slate-700">Nomor HP (opsional)</label>
                    <input type="text" name="phone"
                        class="mt-1 w-full border border-slate-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-indigo-500">
                </div>

                <div>
                    <label class="text-sm font-medium text-slate-700">Password</label>
                    <input type="password" name="password"
                        class="mt-1 w-full border border-slate-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-indigo-500">
                </div>

                <div>
                    <label class="text-sm font-medium text-slate-700">Confirm Password</label>
                    <input type="password" name="password_confirmation"
                        class="mt-1 w-full border border-slate-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-indigo-500">
                </div>

                <button
                    class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2 rounded-md transition">
                    Sign up
                </button>
            </form>

            <p class="mt-6 text-center text-xs text-slate-500">
                Sudah punya akun?
                <a href="{{ route('login') }}" class="font-semibold text-indigo-600 hover:text-indigo-500">
                    Masuk di sini
                </a>
            </p>

    </x-auth-layout>
@endsection
