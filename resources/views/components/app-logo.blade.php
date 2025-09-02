<div class="flex aspect-square size-8 items-center justify-center rounded-md text-accent-foreground">
    {{-- <x-app-logo-icon class="size-5 fill-current text-white dark:text-black" /> --}}
    <img src="{{ asset('storage/' . setting('logo_path')) }}" alt="Logo" class="size-8 fill-current text-white dark:text-black">
</div>
<div class="ms-1 grid flex-1 text-start text-sm">
    <span class="mb-0.5 leading-tight font-semibold">IKM {{ setting('nama_instansi', 'Dasbor') }}</span>
</div>
