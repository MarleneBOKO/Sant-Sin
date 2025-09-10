@extends('../layout/' . $layout)

@section('subhead')
    <title>Update Profile - Midone - Tailwind HTML Admin Template</title>
@endsection

@section('subcontent')
    <div class="intro-y flex items-center mt-8">
        <h2 class="text-lg font-medium mr-auto">Change Password</h2>
    </div>
    <div class="grid grid-cols-12 gap-6">
        <!-- BEGIN: Profile Menu -->
        <div class="col-span-12 lg:col-span-4 xxl:col-span-3 flex lg:block flex-col-reverse">
            <div class="intro-y box mt-5">
                <div class="relative flex items-center p-5">
                    <div class="w-12 h-12 image-fit">
                        <img alt="Midone Tailwind HTML Admin Template" class="rounded-full" src="{{ asset('dist/images/' . $fakers[0]['photos'][0]) }}">
                    </div>
                    <div class="ml-4 mr-auto">
                        <div class="font-medium text-base">{{ $fakers[0]['users'][0]['name'] }}</div>
                        <div class="text-gray-600">{{ $fakers[0]['jobs'][0] }}</div>
                    </div>
                    <div class="dropdown">
                        <a class="dropdown-toggle w-5 h-5 block" href="javascript:;">
                            <i data-feather="more-horizontal" class="w-5 h-5 text-gray-700 dark:text-gray-300"></i>
                        </a>
                        <div class="dropdown-box w-56">
                            <div class="dropdown-box__content box dark:bg-dark-1">
                                <div class="p-4 border-b border-gray-200 dark:border-dark-5 font-medium">Export Options</div>
                                <div class="p-2">
                                    <a href="" class="flex items-center block p-2 transition duration-300 ease-in-out bg-white dark:bg-dark-1 hover:bg-gray-200 dark:hover:bg-dark-2 rounded-md">
                                        <i data-feather="activity" class="w-4 h-4 text-gray-700 dark:text-gray-300 mr-2"></i> English
                                    </a>
                                    <a href="" class="flex items-center block p-2 transition duration-300 ease-in-out bg-white dark:bg-dark-1 hover:bg-gray-200 dark:hover:bg-dark-2 rounded-md">
                                        <i data-feather="box" class="w-4 h-4 text-gray-700 dark:text-gray-300 mr-2"></i> 
                                        Indonesia 
                                        <div class="text-xs text-white px-1 rounded-full bg-theme-6 ml-auto">10</div>
                                    </a>
                                    <a href="" class="flex items-center block p-2 transition duration-300 ease-in-out bg-white dark:bg-dark-1 hover:bg-gray-200 dark:hover:bg-dark-2 rounded-md">
                                        <i data-feather="layout" class="w-4 h-4 text-gray-700 dark:text-gray-300 mr-2"></i> English
                                    </a>
                                    <a href="" class="flex items-center block p-2 transition duration-300 ease-in-out bg-white dark:bg-dark-1 hover:bg-gray-200 dark:hover:bg-dark-2 rounded-md">
                                        <i data-feather="sidebar" class="w-4 h-4 text-gray-700 dark:text-gray-300 mr-2"></i> Indonesia
                                    </a>
                                </div>
                                <div class="px-3 py-3 border-t border-gray-200 dark:border-dark-5 font-medium flex">
                                    <button type="button" class="button button--sm bg-theme-1 text-white">Settings</button>
                                    <button type="button" class="button button--sm bg-gray-200 text-gray-600 dark:bg-dark-5 dark:text-gray-300 ml-auto">View Profile</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="p-5 border-t border-gray-200 dark:border-dark-5">
                    <a class="flex items-center text-theme-1 dark:text-theme-10 font-medium" href="">
                        <i data-feather="activity" class="w-4 h-4 mr-2"></i> Personal Information
                    </a>
                    <a class="flex items-center mt-5" href="">
                        <i data-feather="box" class="w-4 h-4 mr-2"></i> Account Settings
                    </a>
                    <a class="flex items-center mt-5" href="">
                        <i data-feather="lock" class="w-4 h-4 mr-2"></i> Change Password
                    </a>
                    <a class="flex items-center mt-5" href="">
                        <i data-feather="settings" class="w-4 h-4 mr-2"></i> User Settings
                    </a>
                </div>
                <div class="p-5 border-t border-gray-200 dark:border-dark-5">
                    <a class="flex items-center" href="">
                        <i data-feather="activity" class="w-4 h-4 mr-2"></i> Email Settings
                    </a>
                    <a class="flex items-center mt-5" href="">
                        <i data-feather="box" class="w-4 h-4 mr-2"></i> Saved Credit Cards
                    </a>
                    <a class="flex items-center mt-5" href="">
                        <i data-feather="lock" class="w-4 h-4 mr-2"></i> Social Networks
                    </a>
                    <a class="flex items-center mt-5" href="">
                        <i data-feather="settings" class="w-4 h-4 mr-2"></i> Tax Information
                    </a>
                </div>
                <div class="p-5 border-t flex dark:border-dark-5">
                    <button type="button" class="button button--sm block bg-theme-1 text-white">New Group</button>
                    <button type="button" class="button button--sm border text-gray-700 dark:bg-dark-5 dark:text-gray-300 ml-auto">New Quick Link</button>
                </div>
            </div>
        </div>
        <!-- END: Profile Menu -->
        <div class="col-span-12 lg:col-span-8 xxl:col-span-9">
            <!-- BEGIN: Change Password -->
            <div class="intro-y box lg:mt-5">
                <div class="flex items-center p-5 border-b border-gray-200 dark:border-dark-5">
                    <h2 class="font-medium text-base mr-auto">Change Password</h2>
                </div>
                <div class="p-5">
                    <div>
                        <label>Old Password</label>
                        <input type="password" class="input w-full border mt-2" placeholder="Input text">
                    </div>
                    <div class="mt-3">
                        <label>New Password</label>
                        <input type="password" class="input w-full border mt-2" placeholder="Input text">
                    </div>
                    <div class="mt-3">
                        <label>Confirm New Password</label>
                        <input type="password" class="input w-full border mt-2" placeholder="Input text">
                    </div>
                    <button type="button" class="button bg-theme-1 text-white mt-4">Change Password</button>
                </div>
            </div>
            <!-- END: Change Password -->
        </div>
    </div>
@endsection