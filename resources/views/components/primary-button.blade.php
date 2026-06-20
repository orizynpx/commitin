<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center px-4 py-2 bg-primary dark:bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-white dark:text-gray-800 uppercase tracking-widest hover:bg-primary-container dark:hover:bg-surface-container-lowest focus:bg-primary-container dark:focus:bg-surface-container-lowest active:bg-primary-fixed-variant  focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2  transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>
