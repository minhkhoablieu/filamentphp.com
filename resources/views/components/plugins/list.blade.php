<section
    class="mx-auto w-full max-w-8xl px-5 sm:px-10"
    x-cloak
    x-ref="section"
    x-init="
        () => {
            // Initialize the minisearch instance
            searchEngine = new MiniSearch({
                fields: ['name', 'description', 'author.name'],
                searchOptions: {
                    fuzzy: 0.1,
                    prefix: true,
                },
                extractField: (document, fieldName) => {
                    // Enabled access to nested fields
                    return fieldName
                        .split('.')
                        .reduce((doc, key) => doc && doc[key], document)
                },
            })

            // Index the plugins
            searchEngine.addAll(plugins)

            if (reducedMotion) return
            gsap.fromTo(
                $refs.section,
                {
                    autoAlpha: 0,
                    y: 50,
                },
                {
                    autoAlpha: 1,
                    y: 0,
                    duration: 0.7,
                    ease: 'circ.out',
                },
            )
        }
    "
    x-data="{
        searchEngine: null,
        search: '',
        selectedCategories: new Set(),
        selectedVersion: '3',
        selectedPrice: 'All',
        features: {
            dark_theme: false,
            translations: false,
        },

        plugins: @js($plugins),

        currentPage: 1,
        perPage: 24,
        totalItems: 0,
        get totalPages() {
            return Math.ceil(this.totalItems / this.perPage)
        },

        get filteredPlugins() {
            let filterResult = this.plugins

            // Show plugins that are in the selected categories
            if (this.selectedCategories.size > 0) {
                filterResult = filterResult.filter((plugin) =>
                    plugin.categories.some((pluginCategory) =>
                        this.selectedCategories.has(pluginCategory),
                    ),
                )
            }

            // Show plugins that are in the selected version
            filterResult = filterResult.filter((plugin) =>
                plugin.versions.includes(+this.selectedVersion),
            )

            // Show plugins that are in the selected features
            filterResult = filterResult.filter(
                (plugin) =>
                    (this.features.dark_theme
                        ? plugin.features.dark_theme
                        : true) &&
                    (this.features.translations
                        ? plugin.features.translations
                        : true),
            )

            // If the selectedPrice is 'All', show all plugins, if the selectedPrice is 'Free', only show plugins that have a price of 'Free', if the selectedPrice is 'Paid', only show plugins that have a price that is not 'Free'
            filterResult = filterResult.filter(
                (plugin) =>
                    this.selectedPrice === 'All' ||
                    (this.selectedPrice === 'Free' && plugin.price === 'Free') ||
                    (this.selectedPrice === 'Paid' && plugin.price !== 'Free'),
            )

            // If the search is not empty, show plugins that match the search
            if (this.search) {
                const searchResult = this.searchEngine.search(this.search)

                filterResult = filterResult.filter((plugin) =>
                    searchResult.some((result) => result.id === plugin.id),
                )
            }

            // Update the total items
            this.totalItems = filterResult.length

            // Paginate the results
            filterResult = filterResult.slice(
                (this.currentPage - 1) * this.perPage,
                this.currentPage * this.perPage,
            )

            return filterResult
        },
    }"
>
    <div class="flex flex-wrap items-center gap-3 pt-5">
        {{-- Version Switch --}}
        <div class="min-[1170px]:min-w-[15rem]">
            <div
                class="relative z-10 inline-flex select-none items-center gap-2.5 rounded-full bg-white p-[.55rem] font-medium shadow-lg shadow-black/[0.01]"
            >
                <div
                    x-on:click="selectedVersion = '1'"
                    class="relative z-20 w-14 text-center transition duration-300"
                    :class="{
                            'cursor-pointer opacity-50 hover:opacity-100': selectedVersion !== '1',
                            'text-salmon': selectedVersion === '1',
                        }"
                >
                    v1.x
                </div>
                <div
                    class="relative z-20 w-14 text-center transition duration-300"
                    x-on:click="selectedVersion = '2'"
                    :class="{
                            'cursor-pointer opacity-50 hover:opacity-100': selectedVersion !== '2',
                            'text-salmon': selectedVersion === '2',
                        }"
                >
                    v2.x
                </div>
                <div
                    class="relative z-20 w-14 text-center transition duration-300"
                    x-on:click="selectedVersion = '3'"
                    :class="{
                            'cursor-pointer opacity-50 hover:opacity-100': selectedVersion !== '3',
                            'text-salmon': selectedVersion === '3',
                        }"
                >
                    v3.x
                </div>
                <div
                    class="absolute left-[.31rem] top-[.31rem] -z-10 h-8 w-16 rounded-full bg-fair-pink transition duration-300 ease-out will-change-transform"
                    :class="{
                            'translate-x-[4.1rem]': selectedVersion === '2',
                            'translate-x-[8.2rem]': selectedVersion === '3',
                        }"
                ></div>
            </div>
        </div>

        {{-- Search Bar --}}
        <div class="w-full sm:w-auto min-[1170px]:flex-1">
            <div
                class="group/search-bar relative w-full overflow-hidden rounded-full bg-white shadow-lg shadow-black/[0.01] transition duration-200 focus-within:shadow-xl focus-within:shadow-black/[0.02] sm:max-w-xs"
            >
                {{-- Magnify Icon --}}
                <div
                    class="absolute left-1.5 top-1.5 grid h-8 w-8 place-items-center rounded-full bg-fair-pink text-salmon"
                >
                    <svg
                        class="transition duration-200 will-change-transform"
                        xmlns="http://www.w3.org/2000/svg"
                        width="20"
                        height="20"
                        viewBox="0 0 24 24"
                    >
                        <path
                            fill="none"
                            stroke="currentColor"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="m20 20l-4.05-4.05m0 0a7 7 0 1 0-9.9-9.9a7 7 0 0 0 9.9 9.9z"
                        />
                    </svg>
                </div>

                <!-- X icon -->
                <div
                    class="absolute right-0 top-0 grid h-full w-14 place-items-center bg-transparent"
                >
                    <svg
                        class="cursor-pointer text-hurricane transition duration-200 will-change-transform hover:scale-125 hover:text-salmon"
                        x-show="search"
                        x-on:click="search = ''"
                        x-transition:enter="delay-75 ease-out"
                        x-transition:enter-start="translate-x-1 rotate-45 opacity-0"
                        x-transition:enter-end="translate-x-0 rotate-0 opacity-100"
                        x-transition:leave="delay-75 ease-in"
                        x-transition:leave-start="translate-x-0 rotate-0 opacity-100"
                        x-transition:leave-end="translate-x-1 rotate-45 opacity-0"
                        xmlns="http://www.w3.org/2000/svg"
                        width="17"
                        height="17"
                        viewBox="0 0 256 256"
                    >
                        <path
                            fill="currentColor"
                            d="M205.66 194.34a8 8 0 0 1-11.32 11.32L128 139.31l-66.34 66.35a8 8 0 0 1-11.32-11.32L116.69 128L50.34 61.66a8 8 0 0 1 11.32-11.32L128 116.69l66.34-66.35a8 8 0 0 1 11.32 11.32L139.31 128Z"
                        />
                    </svg>
                </div>
                {{-- Search Input --}}
                <input
                    type="text"
                    x-model="search"
                    placeholder="Search ..."
                    class="w-full appearance-none border-none bg-transparent py-3 pl-12 pr-10 text-sm outline-none placeholder:transition placeholder:duration-200 focus:ring-0 group-focus-within/search-bar:placeholder:translate-x-1 group-focus-within/search-bar:placeholder:opacity-0"
                />
            </div>
        </div>

        {{-- Price Toggle --}}
        <div
            class="relative z-10 flex h-11 w-[205px] select-none items-center justify-between gap-1 rounded-full bg-white px-[.55rem] text-sm font-medium shadow-lg shadow-black/[0.01]"
        >
            <div
                x-on:click="selectedPrice = 'All'"
                class="relative z-20 w-14 text-center transition duration-300"
                :class="{
                    'cursor-pointer opacity-50 hover:opacity-100': selectedPrice !== 'All',
                    'text-salmon': selectedPrice === 'All',
                }"
            >
                All
            </div>
            <div
                x-on:click="selectedPrice = 'Free'"
                class="relative z-20 w-14 text-center transition duration-300"
                :class="{
                    'cursor-pointer opacity-50 hover:opacity-100': selectedPrice !== 'Free',
                    'text-salmon': selectedPrice === 'Free',
                }"
            >
                Free
            </div>
            <div
                x-on:click="selectedPrice = 'Paid'"
                class="relative z-20 w-14 text-center transition duration-300"
                :class="{
                    'cursor-pointer opacity-50 hover:opacity-100': selectedPrice !== 'Paid',
                    'text-salmon': selectedPrice === 'Paid',
                }"
            >
                Paid
            </div>
            <div
                class="absolute left-[.35rem] top-[.35rem] -z-10 h-8 w-16 rounded-full bg-fair-pink transition duration-300 ease-out will-change-transform"
                :class="{
                    'translate-x-[4rem]': selectedPrice === 'Free',
                    'translate-x-[8.2rem]': selectedPrice === 'Paid',
                }"
            ></div>
        </div>

        {{-- Vertical Divider --}}
        <div
            class="hidden h-5 w-px rounded-full bg-hurricane/10 min-[1170px]:block"
        ></div>

        {{-- Dark Mode Toggle --}}
        <div
            x-on:click="features.dark_theme = ! features.dark_theme"
            class="group/dark-mode-toggle flex cursor-pointer select-none items-center gap-3 rounded-full py-3 pl-4 pr-6 transition duration-300 hover:shadow-lg hover:shadow-black/[0.01]"
            :class="{
                'bg-fair-pink': features.dark_theme,
                'bg-white': ! features.dark_theme,
            }"
        >
            <div class="text-salmon">
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    width="19"
                    height="19"
                    viewBox="0 0 24 24"
                >
                    <g fill="currentColor">
                        <path
                            d="M19.9 2.307a.483.483 0 0 0-.9 0l-.43 1.095a.484.484 0 0 1-.272.274l-1.091.432a.486.486 0 0 0 0 .903l1.091.432a.48.48 0 0 1 .272.273L19 6.81c.162.41.74.41.9 0l.43-1.095a.484.484 0 0 1 .273-.273l1.091-.432a.486.486 0 0 0 0-.903l-1.091-.432a.484.484 0 0 1-.273-.274l-.43-1.095ZM16.033 8.13a.483.483 0 0 0-.9 0l-.157.399a.484.484 0 0 1-.272.273l-.398.158a.486.486 0 0 0 0 .903l.398.157c.125.05.223.148.272.274l.157.399c.161.41.739.41.9 0l.157-.4a.484.484 0 0 1 .272-.273l.398-.157a.486.486 0 0 0 0-.903l-.398-.158a.484.484 0 0 1-.272-.273l-.157-.4Z"
                        />
                        <path
                            d="M12 22c5.523 0 10-4.477 10-10c0-.463-.694-.54-.933-.143a6.5 6.5 0 1 1-8.924-8.924C12.54 2.693 12.463 2 12 2C6.477 2 2 6.477 2 12s4.477 10 10 10Z"
                        />
                    </g>
                </svg>
            </div>
            <div
                class="text-sm transition duration-300"
                :class="{
                    'text-salmon': features.dark_theme,
                    'opacity-70 text-dolphin group-hover/dark-mode-toggle:opacity-100': ! features.dark_theme,
                }"
            >
                Dark mode
            </div>
        </div>

        {{-- Vertical Divider --}}
        <div
            class="hidden h-5 w-px rounded-full bg-hurricane/10 min-[1170px]:block"
        ></div>

        {{-- Multi Language Toggle --}}
        <div
            x-on:click="features.translations = ! features.translations"
            class="group/multi-language-toggle flex cursor-pointer select-none items-center gap-3 rounded-full py-3 pl-4 pr-6 transition duration-300 hover:shadow-lg hover:shadow-black/[0.01]"
            :class="{
                'bg-fair-pink': features.translations,
                'bg-white': ! features.translations,
            }"
        >
            <div class="text-salmon">
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    width="19"
                    height="19"
                    viewBox="0 0 20 20"
                >
                    <g fill="currentColor">
                        <path
                            d="M7.75 2.75a.75.75 0 0 0-1.5 0v1.258a32.987 32.987 0 0 0-3.599.278a.75.75 0 1 0 .198 1.487A31.545 31.545 0 0 1 8.7 5.545A19.381 19.381 0 0 1 7 9.56a19.418 19.418 0 0 1-1.002-2.05a.75.75 0 0 0-1.384.577a20.935 20.935 0 0 0 1.492 2.91a19.613 19.613 0 0 1-3.828 4.154a.75.75 0 1 0 .945 1.164A21.116 21.116 0 0 0 7 12.331c.095.132.192.262.29.391a.75.75 0 0 0 1.194-.91a18.97 18.97 0 0 1-.59-.815a20.888 20.888 0 0 0 2.333-5.332c.31.031.618.068.924.108a.75.75 0 0 0 .198-1.487a32.832 32.832 0 0 0-3.599-.278V2.75Z"
                        />
                        <path
                            fill-rule="evenodd"
                            d="M13 8a.75.75 0 0 1 .671.415l4.25 8.5a.75.75 0 1 1-1.342.67L15.787 16h-5.573l-.793 1.585a.75.75 0 1 1-1.342-.67l4.25-8.5A.75.75 0 0 1 13 8Zm2.037 6.5L13 10.427L10.964 14.5h4.073Z"
                            clip-rule="evenodd"
                        />
                    </g>
                </svg>
            </div>
            <div
                class="text-sm transition duration-300"
                :class="{
                    'text-salmon': features.translations,
                    'opacity-70 text-dolphin group-hover/dark-mode-toggle:opacity-100': ! features.translations,
                }"
            >
                Multi language
            </div>
        </div>
    </div>

    <div class="flex items-start gap-5 pt-5">
        {{-- Categories --}}
        <div class="hidden w-full max-w-[15rem] sm:block">
            <div class="font-semibold">Categories</div>
            <div class="pt-5">
                @foreach ($categories as $category)
                    <x-plugins.category
                        :name="$category->name"
                        :slug="$category->slug"
                    >
                        <x-slot name="icon">
                            {!! $category->getIcon() !!}
                        </x-slot>
                    </x-plugins.category>
                @endforeach
            </div>
        </div>

        <div class="w-full">
            {{-- Pagination --}}
            <div class="flex items-center justify-between px-1 py-3">
                <div class="flex flex-1 items-center justify-between">
                    <div
                        x-show="filteredPlugins.length"
                        class="text-sm text-gray-700"
                    >
                        Showing
                        <span
                            class="font-extrabold"
                            x-text="(currentPage - 1) * perPage + 1"
                        ></span>
                        to
                        <span
                            class="font-extrabold"
                            x-text="Math.min(currentPage * perPage, totalItems)"
                        ></span>
                        of
                        <span
                            class="font-extrabold"
                            x-text="totalItems"
                        ></span>
                        results
                    </div>
                    <div x-show="!filteredPlugins.length">
                        <div class="text-sm text-gray-700">
                            No results found
                        </div>
                    </div>
                    <x-ui.pagination />
                </div>
            </div>

            <div class="relative min-h-[16rem]">
                {{-- Plugins --}}
                <div
                    x-ref="plugin_cards_wrapper"
                    x-init="
                        () => {
                            autoAnimate($refs.plugin_cards_wrapper)
                        }
                    "
                    class="sticky left-0 top-5 grid w-full grid-cols-[repeat(auto-fill,minmax(20rem,1fr))] items-start justify-center gap-6"
                >
                    <template
                        x-for="plugin in filteredPlugins"
                        :key="plugin.id"
                    >
                        <x-plugins.card />
                    </template>
                </div>

                {{-- No Results Message --}}
                <div
                    x-show="! filteredPlugins.length"
                    x-transition:enter="ease-out"
                    x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100"
                    x-transition:leave="ease-in"
                    x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    class="absolute right-1/2 top-0 grid w-full translate-x-1/2 place-items-center pt-10 transition duration-200"
                >
                    <svg
                        class="text-evening/40"
                        xmlns="http://www.w3.org/2000/svg"
                        width="40"
                        height="40"
                        viewBox="0 0 256 256"
                    >
                        <path
                            fill="currentColor"
                            d="m228.24 219.76l-51.38-51.38a86.15 86.15 0 1 0-8.48 8.48l51.38 51.38a6 6 0 0 0 8.48-8.48ZM38 112a74 74 0 1 1 74 74a74.09 74.09 0 0 1-74-74Z"
                        />
                    </svg>
                    <div class="pt-2 font-semibold text-evening/70">
                        No Results Found
                    </div>
                    <div class="pt-0.5 text-sm text-evening/50">
                        Sorry we couldn't find any plugins matching your search.
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>