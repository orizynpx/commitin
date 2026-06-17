@extends('layouts.app')
@section('title', 'Dashboard')

@section('content')
<div class="mb-8">
    <h2 class="text-2xl font-bold text-[#1e293b] mb-1">Recruitment Overview</h2>
    <p class="text-gray-500 text-sm">Monitor your active talent pipeline and vacancy status across departments.</p>
</div>

<!-- Stats Cards -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    <!-- Card 1 -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <div class="flex justify-between items-start mb-4">
            <div class="p-2 bg-blue-50 rounded-lg text-blue-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
            </div>
            <div class="flex items-center text-emerald-500 text-sm font-medium">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                +12%
            </div>
        </div>
        <h3 class="text-2xl font-bold text-gray-900 mb-1">24</h3>
        <p class="text-gray-500 text-sm">Active Vacancies</p>
    </div>

    <!-- Card 2 -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <div class="flex justify-between items-start mb-4">
            <div class="p-2 bg-teal-50 rounded-lg text-teal-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
            </div>
            <div class="flex items-center text-emerald-500 text-sm font-medium">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                +8%
            </div>
        </div>
        <h3 class="text-2xl font-bold text-gray-900 mb-1">1,284</h3>
        <p class="text-gray-500 text-sm">Total Applicants</p>
    </div>

    <!-- Card 3 -->
    <div class="bg-blue-50 rounded-xl shadow-sm border border-blue-100 p-6">
        <div class="flex justify-between items-start mb-4">
            <div class="p-2 bg-blue-600 rounded-lg text-white shadow-md shadow-blue-200">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
            </div>
        </div>
        <h3 class="text-2xl font-bold text-gray-900 mb-1">48</h3>
        <p class="text-gray-600 text-sm">Interviews Scheduled</p>
    </div>
</div>

<div class="mb-8">
    <div class="flex justify-between items-center mb-4">
        <h3 class="text-lg font-bold text-gray-900">Recent Applications</h3>
        <a href="#" class="text-blue-600 text-sm font-medium hover:text-blue-800 flex items-center">
            View All
            <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
        </a>
    </div>
    
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 text-slate-500 text-xs uppercase tracking-wider border-b border-gray-100">
                        <th class="px-6 py-4 font-semibold">Student Name</th>
                        <th class="px-6 py-4 font-semibold">Applied Position</th>
                        <th class="px-6 py-4 font-semibold">Match Score</th>
                        <th class="px-6 py-4 font-semibold">Date</th>
                        <th class="px-6 py-4 font-semibold text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <!-- Row 1 -->
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-6 py-4">
                            <div class="flex items-center">
                                <div class="w-10 h-10 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center font-bold text-sm mr-3">
                                    AS
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-900">Arjun Sharma</p>
                                    <p class="text-xs text-gray-500">Computer Engineering</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600">
                            Frontend Developer Intern
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center">
                                <div class="w-full bg-gray-200 rounded-full h-2 mr-2 max-w-[100px]">
                                    <div class="bg-emerald-500 h-2 rounded-full" style="width: 92%"></div>
                                </div>
                                <span class="text-xs font-semibold text-gray-700">92%</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500">
                            2 hours ago
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end space-x-3">
                                <button class="text-red-500 hover:text-red-700 p-1">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                </button>
                                <button class="bg-[#1e293b] hover:bg-black text-white px-4 py-1.5 rounded-md text-sm font-medium transition-colors">
                                    Accept
                                </button>
                            </div>
                        </td>
                    </tr>
                    <!-- Row 2 -->
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-6 py-4">
                            <div class="flex items-center">
                                <div class="w-10 h-10 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center font-bold text-sm mr-3">
                                    MW
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-900">Maya Williams</p>
                                    <p class="text-xs text-gray-500">Marketing & Design</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600">
                            UX Research Assistant
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center">
                                <div class="w-full bg-gray-200 rounded-full h-2 mr-2 max-w-[100px]">
                                    <div class="bg-amber-500 h-2 rounded-full" style="width: 78%"></div>
                                </div>
                                <span class="text-xs font-semibold text-gray-700">78%</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500">
                            5 hours ago
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end space-x-3">
                                <button class="text-red-500 hover:text-red-700 p-1">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                </button>
                                <button class="bg-[#1e293b] hover:bg-black text-white px-4 py-1.5 rounded-md text-sm font-medium transition-colors">
                                    Accept
                                </button>
                            </div>
                        </td>
                    </tr>
                    <!-- Row 3 -->
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-6 py-4">
                            <div class="flex items-center">
                                <div class="w-10 h-10 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center font-bold text-sm mr-3">
                                    LC
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-900">Leo Chen</p>
                                    <p class="text-xs text-gray-500">Data Science</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600">
                            Machine Learning Intern
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center">
                                <div class="w-full bg-gray-200 rounded-full h-2 mr-2 max-w-[100px]">
                                    <div class="bg-emerald-500 h-2 rounded-full" style="width: 85%"></div>
                                </div>
                                <span class="text-xs font-semibold text-gray-700">85%</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500">
                            Yesterday
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end space-x-3">
                                <button class="text-red-500 hover:text-red-700 p-1">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                </button>
                                <button class="bg-[#1e293b] hover:bg-black text-white px-4 py-1.5 rounded-md text-sm font-medium transition-colors">
                                    Accept
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Active Vacancies -->
<div>
    <div class="flex justify-between items-center mb-4">
        <h3 class="text-lg font-bold text-gray-900">Active Vacancies</h3>
        <button class="bg-[#0f172a] text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-black transition-colors flex items-center gap-2 shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            Post New Vacancy
        </button>
    </div>
    
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Vacancy Card 1 -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex flex-col h-full">
            <div class="flex justify-between items-start mb-2">
                <h4 class="text-lg font-bold text-[#1e293b]">Lead AI Researcher</h4>
                <span class="bg-emerald-100 text-emerald-700 text-xs font-semibold px-2.5 py-1 rounded-md">Active</span>
            </div>
            <p class="text-xs text-gray-500 uppercase tracking-wider mb-4 font-medium">Dept: Research Lab Alpha</p>
            
            <div class="flex flex-wrap gap-4 text-sm text-gray-600 mb-6 flex-1">
                <div class="flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                    12 Applicants
                </div>
                <div class="flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    Ends Oct 24
                </div>
                <div class="flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    $2,500/mo
                </div>
            </div>
            
            <div class="flex border-t border-gray-100 pt-4 mt-auto">
                <button class="flex-1 flex items-center justify-center gap-2 text-sm font-medium text-gray-600 hover:text-gray-900 border-r border-gray-100 pr-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                    Edit
                </button>
                <button class="flex-1 flex items-center justify-center gap-2 text-sm font-medium text-red-500 hover:text-red-700 pl-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path></svg>
                    Close Vacancy
                </button>
            </div>
        </div>

        <!-- Vacancy Card 2 -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex flex-col h-full">
            <div class="flex justify-between items-start mb-2">
                <h4 class="text-lg font-bold text-[#1e293b]">Social Media Coordinator</h4>
                <span class="bg-emerald-100 text-emerald-700 text-xs font-semibold px-2.5 py-1 rounded-md">Active</span>
            </div>
            <p class="text-xs text-gray-500 uppercase tracking-wider mb-4 font-medium">Dept: University Relations</p>
            
            <div class="flex flex-wrap gap-4 text-sm text-gray-600 mb-6 flex-1">
                <div class="flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                    45 Applicants
                </div>
                <div class="flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    Ends Nov 02
                </div>
                <div class="flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    $1,200/mo
                </div>
            </div>
            
            <div class="flex border-t border-gray-100 pt-4 mt-auto">
                <button class="flex-1 flex items-center justify-center gap-2 text-sm font-medium text-gray-600 hover:text-gray-900 border-r border-gray-100 pr-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                    Edit
                </button>
                <button class="flex-1 flex items-center justify-center gap-2 text-sm font-medium text-red-500 hover:text-red-700 pl-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path></svg>
                    Close Vacancy
                </button>
            </div>
        </div>
    </div>
</div>
@endsection
