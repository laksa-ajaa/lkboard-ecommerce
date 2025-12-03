@extends('layouts.auth', ['title' => 'Login'])

@section('content')
    <x-auth-layout title="Welcome back">

        <div class="w-full max-w-sm bg-white rounded-2xl border border-slate-200/90 p-8 shadow-md shadow-slate-900/10">

            <div class="flex items-center justify-center gap-2 mb-4">
                <span
                    class="inline-flex h-8 w-8 items-center justify-center rounded-xl bg-indigo-500 text-white text-sm font-bold shadow-sm shadow-indigo-500/40">
                    LK
                </span>
                <div class="flex flex-col">
                    <span class="text-sm font-semibold tracking-tight text-slate-900">
                        LKBoard
                    </span>
                    <span class="text-[10px] uppercase tracking-[0.18em] text-slate-400">
                        Mechanical Keyboards
                    </span>
                </div>
            </div>

            <h1 class="text-xl font-bold text-center">Welcome back</h1>
            <p class="text-sm text-center text-slate-500 mt-1">Sign in to continue</p>

            {{-- FORM --}}
            <form action="{{ route('login.store') }}" method="POST" class="mt-6 space-y-4">
                @csrf

                <div>
                    <label class="text-sm font-medium text-slate-700">Email</label>
                    <input type="email" name="email" value="{{ old('email') }}"
                        class="mt-1 w-full border border-slate-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-indigo-500">
                </div>

                <div>
                    <label class="text-sm font-medium text-slate-700">Password</label>
                    <input type="password" name="password"
                        class="mt-1 w-full border border-slate-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-indigo-500">
                </div>

                <div class="flex items-center justify-between">
                    <label class="inline-flex items-center gap-2 text-xs text-slate-600">
                        <input type="checkbox" name="remember" value="1"
                            class="h-3.5 w-3.5 rounded border-slate-300 text-indigo-500 focus:ring-indigo-500">
                        <span>Ingat saya di perangkat ini</span>
                    </label>
                </div>

                <button
                    class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2 rounded-md transition">
                    Sign in
                </button>
            </form>

            {{-- SOCIAL LOGIN --}}
            <div class="mt-8 text-center text-sm text-slate-500">or continue with</div>

            <div class="mt-4 flex justify-center gap-4">
                <button
                    class="w-12 h-12 rounded-full border border-slate-300 flex items-center justify-center hover:bg-slate-50">
                    <img src="/icons/google.svg" class="w-5">
                </button>

                <button
                    class="w-12 h-12 rounded-full border border-slate-300 flex items-center justify-center hover:bg-slate-50">
                    <img src="/icons/facebook.svg" class="w-5">
                </button>
            </div>

            <p class="mt-6 text-center text-xs text-slate-500">
                Belum punya akun?
                <a href="{{ route('register') }}" class="font-semibold text-indigo-600 hover:text-indigo-500">
                    Daftar sekarang
                </a>
            </p>

    </x-auth-layout>
@endsection
