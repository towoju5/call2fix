@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4">
    <div class="flex justify-center">
        <div class="w-full md:w-2/3 lg:w-1/2">
            <div class="bg-white shadow-md rounded-lg">
                <div class="bg-gray-100 px-4 py-3 border-b border-gray-200 rounded-t-lg">
                    <h2 class="text-lg font-semibold text-gray-800">{{ __('Two Factor Authentication') }}</h2>
                </div>

                <div class="p-4">
                    <form method="POST" action="{{ route('admin.2fa.verify') }}">
                        @csrf

                        <div class="mb-4">
                            <label for="code" class="block text-sm font-medium text-gray-700 mb-2">{{ __('Authentication Code') }}</label>
                            <input id="code" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('code') border-red-500 @enderror" name="code" required autocomplete="off" autofocus>

                            @error('code')
                                <p class="mt-2 text-sm text-red-600">
                                    <strong>{{ $message }}</strong>
                                </p>
                            @enderror
                        </div>

                        <div class="flex justify-end">
                            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                                {{ __('Verify') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
