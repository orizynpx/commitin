<button {{ $attributes->merge(['type' => 'button', 'class' => 'inline-flex items-center px-4 py-2 bg-secondary-container dark:bg-primary border border-secondary-container dark:border-gray-500 rounded-md font-semibold text-xs text-on-secondary-container  uppercase tracking-widest shadow-sm hover:bg-gray-50 dark:hover:bg-primary-container focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2  disabled:opacity-25 transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>
