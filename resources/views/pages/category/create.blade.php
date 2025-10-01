@extends('layouts.layout')
@section('title', 'Add Category')
@section('subtitle', 'Fill out the form to add a new category')
@section('content')

    <div class="space-y-6">

        <div class="rounded-2xl border border-gray-200 bg-white shadow-sm">
            <!-- Form -->
            <div class="p-5 sm:p-6">
                <form action="{{ route('category.store') }}" method="POST" class="space-y-5">
                    @csrf

                    <div>
                        <label for="category_name" class="block text-sm font-medium text-gray-700">
                            Category Name
                            <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="category_name" name="category_name" value="{{ old('category_name') }}"
                            class="mt-1 block w-full rounded-lg border border-gray-300 shadow-sm focus:border-blue-400 focus:ring focus:ring-blue-100 sm:text-sm p-2.5"
                            placeholder="Enter category name" required>
                        @error('category_name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex justify-end gap-3">
                        <a href="{{ route('category.index') }}"
                            class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50">
                            Cancel
                        </a>
                        <button type="submit"
                            class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 focus:ring focus:ring-blue-200">
                            Save
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection
