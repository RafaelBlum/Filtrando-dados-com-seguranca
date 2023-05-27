<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>



    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <form action="{{route('dashboard')}}" method="post">
                            @csrf

                            <input type="text" id="first_name" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm
                                rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5
                                dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500
                                dark:focus:border-blue-500" name="search" placeholder="Search users" required value="{{old('search')}}">

                            <button type="submit" class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 mr-2 mb-2 dark:bg-blue-600 dark:hover:bg-blue-700 focus:outline-none dark:focus:ring-blue-800">Submit</button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="bg-gray-500 p-4 text-white mb-4 mt-4 sm:rounded-lg">
                {{request('search')}}
            </div>

            <div >
                <div >
                    <ul>
                        @foreach($users as $user)
                             <li class="p-0 text-gray-500 text-gray-100 dark:text-gray-100">{{$user->id}}::{{$user->name}} | <span class="focus:outline-none text-white bg-green-700 font-medium rounded-lg text-sm px-5 py-2.5 mr-2 mb-2">{{($user->admin ? 'ADMIN': 'USER')}}</span></li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
