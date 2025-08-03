<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <!-- Brand Logo -->
    <a href="{{ route('admin.home') }}" class="brand-link">
        @if($globalSetting?->logo)
            <img src="{{ asset('storage/' . $globalSetting->logo) }}" alt="Logo" class="brand-image img-circle elevation-3" style="opacity: .8">
        @endif
        <span class="brand-text font-weight-light">{{ $globalSetting?->store_name ?? 'اسم المتجر' }}</span>
    </a>

    <!-- Sidebar -->
    <div class="sidebar">
        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                <!-- الصفحة الرئيسية -->
                <li class="nav-item">
                    <a href="{{ route('admin.home') }}" class="nav-link {{ request()->routeIs('admin.home') ? 'active' : '' }}">
                        <!-- <svg class="nav-icon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="#0984e3" viewBox="0 0 24 24">
                            <path d="M3 9.5L12 4l9 5.5M4 10v10h16V10" />
                        </svg> -->
                        <svg class="nav-icon" width="20" height="20" fill="#0984e3" viewBox="0 0 24 24">
                            <path d="M3 9.5L12 4l9 5.5M4 10v10h16V10" />
                        </svg>
                        <p class="ms-2">الصفحة الرئيسية</p>
                    </a>
                </li>

                <!-- التصنيفات -->
                <li class="nav-item">
                    <a href="{{ route('admin.categories.index') }}" class="nav-link {{ request()->is('admin/categories*') ? 'active' : '' }}">
                        <!-- <svg class="nav-icon" width="20" height="20" fill="#fdcb6e" viewBox="0 0 24 24">
                            <path d="M3 3h8v8H3V3zm0 10h8v8H3v-8zm10-10h8v8h-8V3zm0 10h8v8h-8v-8z" />
                        </svg> -->
                        <svg class="nav-icon" width="20" height="20" fill="#fdcb6e" viewBox="0 0 24 24">
                            <path d="M3 3h8v8H3V3zm0 10h8v8H3v-8zm10-10h8v8h-8V3zm0 10h8v8h-8v-8z" />
                        </svg>
                        <p class="ms-2">التصنيفات</p>
                    </a>
                </li>
                <!-- العلامات التجارية -->
                <li class="nav-item">
                    <a href="{{ route('admin.brands.index') }}" class="nav-link {{ request
                        ()->is('admin/brands*') ? 'active' : '' }}">
                        <!-- <svg class="nav-icon" width="20" height="20" fill="#6c5ce7" viewBox="0 0 24 24">
                            <path d="M3 3h18v18H3V3zm2 2v14h14V5H5z" />
                        </svg> -->
                        <svg class="nav-icon" width="20" height="20" fill="#6c5ce7" viewBox="0 0 24 24">
                            <circle cx="12" cy="12" r="10"/>
                            <text x="12" y="16" text-anchor="middle" font-size="10" fill="#fff" font-family="Arial">B</text>
                        </svg>
                        <p class="ms-2">العلامات التجارية</p>
                    </a>

                <!-- المنتجات -->
                <li class="nav-item">
                    <a href="{{ route('admin.products.index') }}" class="nav-link {{ request()->is('admin/products*') ? 'active' : '' }}">
                        <!-- <svg class="nav-icon" width="20" height="20" fill="#e17055" viewBox="0 0 24 24">
                            <path d="M4 4h16v16H4V4zm2 2v12h12V6H6z" />
                        </svg> -->
                        <svg class="nav-icon" width="20" height="20" fill="#e17055" viewBox="0 0 24 24">
                            <rect x="3" y="7" width="18" height="13" rx="2"/>
                            <path d="M3 7l9-5 9 5"/>
                        </svg>
                        <p class="ms-2">المنتجات</p>
                    </a>
                </li>

                <!-- العملاء -->
                <li class="nav-item">
                    <a href="{{ route('admin.customers.index') }}" class="nav-link {{ request()->is('admin/customers*') ? 'active' : '' }}">
                        <!-- <svg class="nav-icon" width="20" height="20" fill="#6c5ce7" viewBox="0 0 24 24">
                            <path d="M12 12c2.7 0 5-2.3 5-5s-2.3-5-5-5-5 2.3-5 5 2.3 5 5 5zm0 2c-3.3 0-10 1.7-10 5v3h20v-3c0-3.3-6.7-5-10-5z" />
                        </svg> -->
                        <svg class="nav-icon" width="20" height="20" fill="#6c5ce7" viewBox="0 0 24 24">
                            <circle cx="12" cy="8" r="4"/>
                            <path d="M4 20c0-4 16-4 16 0"/>
                        </svg>
                        <p class="ms-2">العملاء</p>
                    </a>
                </li>

                <!-- المبيعات -->
                <li class="nav-item">
                    <a href="{{ route('admin.sales.index') }}" class="nav-link {{ request()->is('admin/sales*') ? 'active' : '' }}">
                        <!-- <svg class="nav-icon" width="20" height="20" fill="#00b894" viewBox="0 0 24 24">
                            <path d="M7 18c-1.1 0-2-.9-2-2V5H3v11c0 2.2 1.8 4 4 4h11v-2H7zM21 3H8c-1.1 0-2 .9-2 2v9c0 1.1.9 2 2 2h13c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2z" />
                        </svg> -->
                        <svg class="nav-icon" width="20" height="20" fill="#00b894" viewBox="0 0 24 24">
                            <path d="M3 17l6-6 4 4 8-8"/>
                            <circle cx="19" cy="5" r="2"/>
                        </svg>
                        <p class="ms-2">المبيعات</p>
                    </a>
                </li>

                <!-- فواتير الصيانة -->
                <li class="nav-item">
                    <a href="{{ route('admin.repairs.index') }}" class="nav-link {{ request()->is('admin/repairs*') ? 'active' : '' }}">
                        <!-- <svg class="nav-icon" width="20" height="20" fill="#fab1a0" viewBox="0 0 24 24">
                            <path d="M20.7 5.3l-2-2c-.4-.4-1-.4-1.4 0L14 6.6 17.4 10l3.3-3.3c.4-.4.4-1 0-1.4zM3 17.25V21h3.75l11-11.03-3.75-3.75L3 17.25z"/>
                        </svg> -->
                        <svg class="nav-icon" width="20" height="20" fill="#fab1a0" viewBox="0 0 24 24">
                            <path d="M21 7l-1-1-7 7-4-4-1 1 5 5z"/>
                            <rect x="3" y="3" width="18" height="18" rx="2"/>
                        </svg>
                        <p class="ms-2">فواتير الصيانة</p>
                    </a>
                </li>

                <!-- المشتريات -->
                <li class="nav-item">
                    <a href="{{ route('admin.purchases.index') }}" class="nav-link {{ request()->is('admin/purchases*') ? 'active' : '' }}">
                        <!-- <svg class="nav-icon" width="20" height="20" fill="#ffeaa7" viewBox="0 0 24 24">
                            <path d="M3 3h18v2H3V3zm0 4h12v2H3V7zm0 4h18v2H3v-2z"/>
                        </svg> -->
                        <svg class="nav-icon" width="20" height="20" fill="#ffeaa7" viewBox="0 0 24 24">
                            <path d="M6 6h15l-1.5 9h-13z"/>
                            <circle cx="9" cy="21" r="1"/>
                            <circle cx="19" cy="21" r="1"/>
                        </svg>
                        <p class="ms-2">المشتريات</p>
                    </a>
                </li>

                <!-- الموردين -->
                <li class="nav-item">
                    <a href="{{ route('admin.suppliers.index') }}" class="nav-link {{ request()->is('admin/suppliers*') ? 'active' : '' }}">
                        <!-- <svg class="nav-icon" width="20" height="20" fill="#00cec9" viewBox="0 0 24 24">
                            <path d="M16 11c1.7 0 3-1.3 3-3S17.7 5 16 5s-3 1.3-3 3 1.3 3 3 3zM8 11c1.7 0 3-1.3 3-3S9.7 5 8 5 5 6.3 5 8s1.3 3 3 3zm8 2c-2 0-6 1-6 3v2h12v-2c0-2-4-3-6-3zM8 13c-.3 0-.7 0-1 .1 1.2.8 2 1.8 2 2.9v2H3v-2c0-1.6 3-3 5-3z"/>
                        </svg> -->
                        <svg class="nav-icon" width="20" height="20" fill="#00cec9" viewBox="0 0 24 24">
                            <circle cx="12" cy="8" r="4"/>
                            <path d="M2 20c0-4 8-6 10-6s10 2 10 6"/>
                        </svg>
                        <p class="ms-2">الموردين</p>
                    </a>
                </li>

                <!-- المصروفات -->
                <li class="nav-item">
                    <a href="{{ route('admin.expenses.index') }}" class="nav-link {{ request()->is('admin/expenses*') ? 'active' : '' }}">
                        <!-- <svg class="nav-icon" width="20" height="20" fill="#fd79a8" viewBox="0 0 24 24">
                            <path d="M12 8c-1.1 0-2 .9-2 2h-2l3 3 3-3h-2c0-.55-.45-1-1-1zm0 8c1.1 0 2-.9 2-2h2l-3-3-3 3h2c0 .55.45 1 1 1zm10-2v2c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2v-2H0v2c0 2.2 1.8 4 4 4h16c2.2 0 4-1.8 4-4v-2h-2z"/>
                        </svg> -->
                        <svg class="nav-icon" width="20" height="20" fill="#fd79a8" viewBox="0 0 24 24">
                            <rect x="2" y="7" width="20" height="10" rx="2"/>
                            <rect x="8" y="11" width="8" height="2" rx="1" fill="#fff"/>
                        </svg>
                        <p class="ms-2">المصروفات</p>
                    </a>
                </li>


                 <!-- المحافظ الإلكترونية -->
                <li class="nav-item">
                    <a href="{{ route('admin.wallets.index') }}" class="nav-link {{ request()->is('admin/wallets*') ? 'active' : '' }}">
                        <!-- <svg class="nav-icon" width="20" height="20" fill="#55efc4" viewBox="0 0 24 24">
                            <path d="M2 7c0-1.1.9-2 2-2h16c1.1 0 2 .9 2 2v2H2V7zm0 4h20v6c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2v-6z" />
                        </svg> -->
                        <svg class="nav-icon" width="20" height="20" fill="#55efc4" viewBox="0 0 24 24">
                            <rect x="2" y="7" width="20" height="10" rx="2"/>
                            <circle cx="7" cy="12" r="2"/>
                        </svg>
                        <p class="ms-2">المحافظ</p>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="{{ route('admin.wallet_providers.index') }}" class="nav-link {{ request()->is('admin/wallet_providers*') ? 'active' : '' }}">
                        <!-- <svg class="nav-icon" width="20" height="20" fill="#81ecec" viewBox="0 0 24 24">
                            <path d="M12 2a10 10 0 100 20 10 10 0 000-20zm1 14.5h-2v-2h2v2zm0-4h-2V7h2v5.5z" />
                        </svg> -->
                        <svg class="nav-icon" width="20" height="20" fill="#81ecec" viewBox="0 0 24 24">
                            <path d="M12 2a10 10 0 100 20 10 10 0 000-20zm1 14.5h-2v-2h2v2zm0-4h-2V7h2v5.5z"/>
                        </svg>
                        <p class="ms-2">مزودو المحافظ</p>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="{{ route('admin.wallet_transactions.index') }}" class="nav-link {{ request()->is('admin/wallet_transactions*') ? 'active' : '' }}">
                        <!-- <svg class="nav-icon" width="20" height="20" fill="#00cec9" viewBox="0 0 24 24">
                            <path d="M12 8c-2.2 0-4 1.8-4 4s1.8 4 4 4 4-1.8 4-4-1.8-4-4-4zm0-6C6.5 2 2 6.5 2 12s4.5 10 10 10 10-4.5 10-10S17.5 2 12 2z" />
                        </svg> -->
                        <svg class="nav-icon" width="20" height="20" fill="#00cec9" viewBox="0 0 24 24">
                            <path d="M12 8c-2.2 0-4 1.8-4 4s1.8 4 4 4 4-1.8 4-4-1.8-4-4-4zm0-6C6.5 2 2 6.5 2 12s4.5 10 10 10 10-4.5 10-10S17.5 2 12 2z" />
                        </svg>
                        <p class="ms-2">حركات المحافظ</p>
                    </a>
                </li>
                <!-- التقارير -->
                <li class="nav-item has-treeview {{ request()->is('admin/reports/*') ? 'menu-open' : '' }}">
                    <a href="#" class="nav-link {{ request()->is('admin/reports/*') ? 'active' : '' }}">
                        <!-- <svg class="nav-icon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="#0984e3" viewBox="0 0 24 24">
                            <path d="M3 13h6v8H3zm12-6h6v14h-6zM9 9h6v12H9z"/>
                        </svg> -->
                        <svg class="nav-icon" width="20" height="20" fill="#0984e3" viewBox="0 0 24 24">
                            <path d="M3 13h6v8H3zm12-6h6v14h-6zM9 9h6v12H9z"/>
                        </svg>
                        <p class="ms-2">التقارير<i class="right fas fa-angle-left ms-2"></i></p>
                    </a>
                    <ul class="nav nav-treeview pl-3">
                        <li class="nav-item">
                            <a href="{{ route('admin.reports.sales') }}" class="nav-link {{ request()->routeIs('admin.reports.sales') ? 'active' : '' }}">
                                <!-- <svg class="nav-icon" width="16" height="16" fill="#6ab04c" viewBox="0 0 24 24">
                                    <path d="M3 17h18v2H3v-2zm0-4h12v2H3v-2zm0-4h6v2H3V9z"/>
                                </svg> -->
                                <svg class="nav-icon" width="16" height="16" fill="#6ab04c" viewBox="0 0 24 24">
                                    <path d="M3 17h18v2H3v-2zm0-4h12v2H3v-2zm0-4h6v2H3V9z"/>
                                </svg>
                                <p class="ms-2">تقرير المبيعات</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.reports.purchases') }}" class="nav-link {{ request()->routeIs('admin.reports.purchases') ? 'active' : '' }}">
                                <!-- <svg class="nav-icon" width="16" height="16" fill="#e17055" viewBox="0 0 24 24">
                                    <path d="M3 3h18v2H3V3zm0 4h12v2H3V7zm0 4h18v2H3v-2z"/>
                                </svg> -->
                                <svg class="nav-icon" width="16" height="16" fill="#e17055" viewBox="0 0 24 24">
                                    <path d="M3 3h18v2H3V3zm0 4h12v2H3V7zm0 4h18v2H3v-2z"/>
                                </svg>
                                <p class="ms-2">تقرير المشتريات</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.reports.repairs') }}" class="nav-link {{ request()->routeIs('admin.reports.repairs') ? 'active' : '' }}">
                                <!-- <svg class="nav-icon" width="16" height="16" fill="#e17055" viewBox="0 0 24 24">
                                    <path d="M3 3h18v2H3V3zm0 4h12v2H3V7zm0 4h18v2H3v-2z"/>
                                </svg> -->
                                <svg class="nav-icon" width="16" height="16" fill="#e17055" viewBox="0 0 24 24">
                                    <path d="M3 3h18v2H3V3zm0 4h12v2H3V7zm0 4h18v2H3v-2z"/>
                                </svg>
                                <p class="ms-2">تقرير الصيانه</p>
                            </a>
                        </li>
                    </ul>
                </li>

                <!-- الاعدادات -->
                <li class="nav-item">
                    <a href="{{ route('admin.settings.edit') }}" class="nav-link {{ request()->is('admin/settings*') ? 'active' : '' }}">
                        <!-- <svg class="nav-icon" width="20" height="20" fill="#636e72" viewBox="0 0 24 24">
                            <path d="M19.4 15a7.95 7.95 0 0 0 .6-3 7.95 7.95 0 0 0-.6-3l2.1-1.6-2-3.5-2.5 1a8.2 8.2 0 0 0-2.6-1.5l-.4-2.6h-4l-.4 2.6A8.2 8.2 0 0 0 6 4.9l-2.5-1-2 3.5 2.1 1.6a7.95 7.95 0 0 0 0 6L1.5 16.6l2 3.5 2.5-1a8.2 8.2 0 0 0 2.6 1.5l.4 2.6h4l.4-2.6a8.2 8.2 0 0 0 2.6-1.5l2.5 1 2-3.5-2.1-1.6zM12 16a4 4 0 1 1 0-8 4 4 0 0 1 0 8z"/>
                        </svg> -->
                        <svg class="nav-icon" width="20" height="20" fill="#636e72" viewBox="0 0 24 24">
                            <path d="M19.4 15a7.95 7.95 0 0 0 .6-3 7.95 7.95 0 0 0-.6-3l2.1-1.6-2-3.5-2.5 1a8.2 8.2 0 0 0-2.6-1.5l-.4-2.6h-4l-.4 2.6A8.2 8.2 0 0 0 6 4.9l-2.5-1-2 3.5 2.1 1.6a7.95 7.95 0 0 0 0 6L1.5 16.6l2 3.5 2.5-1a8.2 8.2 0 0 0 2.6 1.5l.4 2.6h4l.4-2.6a8.2 8.2 0 0 0 2.6-1.5l2.5 1 2-3.5-2.1-1.6zM12 16a4 4 0 1 1 0-8 4 4 0 0 1 0 8z"/>
                        </svg>
                        <p class="ms-2">الإعدادات</p>
                    </a>
                </li>
                <!-- الفروع -->
                <li class="nav-item">
                    <a href="{{ route('admin.branches.index') }}" class="nav-link {{ request()->is('admin/branches*') ? 'active' : '' }}">
                        <!-- <svg class="nav-icon" width="20" height="20" fill="#dfe6e9" viewBox="0 0 24 24">
                            <path d="M3 3h18v18H3V3zm2 2v14h14V5H5z"/>
                        </svg> -->
                        <svg class="nav-icon" width="20" height="20" fill="#dfe6e9" viewBox="0 0 24 24">
                            <rect x="3" y="3" width="18" height="18" rx="2"/>
                            <path d="M7 7v10M17 7v10"/>
                        </svg>
                        <p class="ms-2">الفروع</p>
                    </a>
                </li>
            </ul>
        </nav>
    </div>
</aside>
