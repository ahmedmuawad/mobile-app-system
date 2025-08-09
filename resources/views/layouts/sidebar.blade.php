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
            @php
                $user = auth()->user();

            $modules = $user->role === 'super_admin' ? ['all'] : $user->company->package->modules->pluck('slug')->toArray();
            @endphp
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                <!-- الصفحة الرئيسية -->
                <li class="nav-item">
                    <a href="{{ route('admin.home') }}" class="nav-link {{ request()->routeIs('admin.home') ? 'active' : '' }}">
                        <svg class="nav-icon" width="20" height="20" fill="#0984e3" viewBox="0 0 24 24">
                            <path d="M3 9.5L12 4l9 5.5M4 10v10h16V10" />
                        </svg>
                        <p class="ms-2">الصفحة الرئيسية</p>
                    </a>
                </li>

                @if(in_array('packages', $modules) || auth()->user()->role === 'super_admin')
                <!-- الباقات -->
                <li class="nav-item">
                    <a href="{{ route('admin.packages.index') }}" class="nav-link {{ request()->is('admin/packages*') ? 'active' : '' }}">
                        <svg class="nav-icon" width="16" height="16" fill="#6c5ce7" viewBox="0 0 24 24">
                            <rect x="3" y="3" width="18" height="18" rx="2"/>
                            <path d="M7 7h10v10H7z"/>
                        </svg>
                        <p class="ms-2">الباقات</p>
                    </a>
                </li>
                @endif
                @if(in_array('modules', $modules) || auth()->user()->role === 'super_admin')

                <!-- الموديولز -->
                <li class="nav-item">
                    <a href="{{ route('admin.modules.index') }}" class="nav-link {{ request()->is('admin/modules*') ? 'active' : '' }}">
                        <svg class="nav-icon" width="16" height="16" fill="#6c5ce7" viewBox="0 0 24 24">
                            <rect x="3" y="3" width="18" height="18" rx="2"/>
                            <path d="M7 7h10v10H7z"/>
                        </svg>
                        <p class="ms-2">الموديولز</p>
                    </a>
                </li>
                @endif
                <!-- الاشتراكات -->
                @if(in_array('subscriptions', $modules) || auth()->user()->role === 'super_admin')
                <li class="nav-item">
                    <a href="{{ route('admin.subscriptions.index') }}" class="nav-link {{ request()->is('admin/subscriptions*') ? 'active' : '' }}">
                        <svg class="nav-icon" width="16" height="16" fill="#6c5ce7" viewBox="0 0 24 24">
                            <rect x="3" y="3" width="18" height="18" rx="2"/>
                            <path d="M7 7h10v10H7z"/>
                        </svg>
                        <p class="ms-2">الاشتراكات</p>
                    </a>
                </li>
                @endif
                <!-- الفروع -->
                @if(in_array('branches', $modules) || auth()->user()->role === 'super_admin')
                <li class="nav-item">
                    <a href="{{ route('admin.branches.index') }}" class="nav-link {{ request()->is('admin/branches*') ? 'active' : '' }}">
                        <svg class="nav-icon" width="20" height="20" fill="#dfe6e9" viewBox="0 0 24 24">
                            <rect x="3" y="3" width="18" height="18" rx="2"/>
                            <path d="M7 7v10M17 7v10"/>
                        </svg>
                        <p class="ms-2">الفروع</p>
                    </a>
                </li>
                @endif
                <!-- التصنيفات -->
                @if(in_array('categories', $modules) || auth()->user()->role === 'super_admin')
                <li class="nav-item">
                    <a href="{{ route('admin.categories.index') }}" class="nav-link {{ request()->is('admin/categories*') ? 'active' : '' }}">
                        <svg class="nav-icon" width="20" height="20" fill="#fdcb6e" viewBox="0 0 24 24">
                            <path d="M3 3h8v8H3V3zm0 10h8v8H3v-8zm10-10h8v8h-8V3zm0 10h8v8h-8v-8z" />
                        </svg>
                        <p class="ms-2">التصنيفات</p>
                    </a>
                </li>
                @endif

                <!-- العلامات التجارية -->
                @if(in_array('brands', $modules) || auth()->user()->role === 'super_admin')
                <li class="nav-item">
                    <a href="{{ route('admin.brands.index') }}" class="nav-link {{ request()->is('admin/brands*') ? 'active' : '' }}">
                        <svg class="nav-icon" width="20" height="20" fill="#6c5ce7" viewBox="0 0 24 24">
                            <circle cx="12" cy="12" r="10"/>
                            <text x="12" y="16" text-anchor="middle" font-size="10" fill="#fff" font-family="Arial">B</text>
                        </svg>
                        <p class="ms-2">العلامات التجارية</p>
                    </a>
                </li>
                @endif

                <!-- المنتجات -->
                @if(in_array('products', $modules) || auth()->user()->role === 'super_admin')
                <li class="nav-item">
                    <a href="{{ route('admin.products.index') }}" class="nav-link {{ request()->is('admin/products*') ? 'active' : '' }}">
                        <svg class="nav-icon" width="20" height="20" fill="#e17055" viewBox="0 0 24 24">
                            <rect x="3" y="7" width="18" height="13" rx="2"/>
                            <path d="M3 7l9-5 9 5"/>
                        </svg>
                        <p class="ms-2">المنتجات</p>
                    </a>
                </li>
                @endif
                <!-- العملاء -->
                @if(in_array('customers', $modules) || auth()->user()->role === 'super_admin')
                <li class="nav-item">
                    <a href="{{ route('admin.customers.index') }}" class="nav-link {{ request()->is('admin/customers*') ? 'active' : '' }}">
                        <svg class="nav-icon" width="20" height="20" fill="#6c5ce7" viewBox="0 0 24 24">
                            <circle cx="12" cy="8" r="4"/>
                            <path d="M4 20c0-4 16-4 16 0"/>
                        </svg>
                        <p class="ms-2">العملاء</p>
                    </a>
                </li>
                @endif

                <!-- المبيعات -->
                @if(in_array('sales', $modules) || auth()->user()->role === 'super_admin')
                <li class="nav-item">
                    <a href="{{ route('admin.sales.index') }}" class="nav-link {{ request()->is('admin/sales*') ? 'active' : '' }}">
                        <svg class="nav-icon" width="20" height="20" fill="#00b894" viewBox="0 0 24 24">
                            <path d="M3 17l6-6 4 4 8-8"/>
                            <circle cx="19" cy="5" r="2"/>
                        </svg>
                        <p class="ms-2">المبيعات</p>
                    </a>
                </li>
                @endif

                <!-- طرق الدفع -->
                @if(in_array('sales', $modules) || auth()->user()->role === 'super_admin')
                <li class="nav-item">
                    <a href="{{ route('admin.payment-methods.index') }}" class="nav-link {{ request()->is('admin/payment-methods*') ? 'active' : '' }}">
                        <svg class="nav-icon" width="20" height="20" fill="#00b894" viewBox="0 0 24 24">
                            <path d="M3 17l6-6 4 4 8-8"/>
                            <circle cx="19" cy="5" r="2"/>
                        </svg>
                        <p class="ms-2">طرق الدفع </p>
                    </a>
                </li>
                @endif

                <!-- فواتير الصيانة -->
                @if(in_array('repairs', $modules) || auth()->user()->role === 'super_admin')
                <li class="nav-item">
                    <a href="{{ route('admin.repairs.index') }}" class="nav-link {{ request()->is('admin/repairs*') ? 'active' : '' }}">
                        <svg class="nav-icon" width="20" height="20" fill="#fab1a0" viewBox="0 0 24 24">
                            <path d="M21 7l-1-1-7 7-4-4-1 1 5 5z"/>
                            <rect x="3" y="3" width="18" height="18" rx="2"/>
                        </svg>
                        <p class="ms-2">فواتير الصيانة</p>
                    </a>
                </li>
                @endif

                <!-- المشتريات -->
                @if(in_array('purchases', $modules) || auth()->user()->role === 'super_admin')
                <li class="nav-item">
                    <a href="{{ route('admin.purchases.index') }}" class="nav-link {{ request()->is('admin/purchases*') ? 'active' : '' }}">
                        <svg class="nav-icon" width="20" height="20" fill="#ffeaa7" viewBox="0 0 24 24">
                            <path d="M6 6h15l-1.5 9h-13z"/>
                            <circle cx="9" cy="21" r="1"/>
                            <circle cx="19" cy="21" r="1"/>
                        </svg>
                        <p class="ms-2">المشتريات</p>
                    </a>
                </li>
                @endif

                <!-- الموردين -->
                @if(in_array('suppliers', $modules) || auth()->user()->role === 'super_admin')
                <li class="nav-item">
                    <a href="{{ route('admin.suppliers.index') }}" class="nav-link {{ request()->is('admin/suppliers*') ? 'active' : '' }}">
                        <svg class="nav-icon" width="20" height="20" fill="#00cec9" viewBox="0 0 24 24">
                            <circle cx="12" cy="8" r="4"/>
                            <path d="M2 20c0-4 8-6 10-6s10 2 10 6"/>
                        </svg>
                        <p class="ms-2">الموردين</p>
                    </a>
                </li>
                @endif
                <!-- المصروفات -->
                @if(in_array('expenses', $modules) || auth()->user()->role === 'super_admin')
                <li class="nav-item">
                    <a href="{{ route('admin.expenses.index') }}" class="nav-link {{ request()->is('admin/expenses*') ? 'active' : '' }}">
                        <svg class="nav-icon" width="20" height="20" fill="#fd79a8" viewBox="0 0 24 24">
                            <rect x="2" y="7" width="20" height="10" rx="2"/>
                            <rect x="8" y="11" width="8" height="2" rx="1" fill="#fff"/>
                        </svg>
                        <p class="ms-2">المصروفات</p>
                    </a>
                </li>
                @endif

                <!-- المحافظ الإلكترونية -->
                @if(in_array('wallets', $modules) || auth()->user()->role === 'super_admin')
                <li class="nav-item has-treeview {{ request()->is('admin/wallets*') || request()->is('admin/wallet_providers*') || request()->is('admin/wallet_transactions*') ? 'menu-open' : '' }}">
                    <a href="#" class="nav-link {{ request()->is('admin/wallets*') || request()->is('admin/wallet_providers*') || request()->is('admin/wallet_transactions*') ? 'active' : '' }}">
                        <svg class="nav-icon" width="20" height="20" fill="#55efc4" viewBox="0 0 24 24">
                            <rect x="2" y="7" width="20" height="10" rx="2"/>
                            <circle cx="7" cy="12" r="2"/>
                        </svg>
                        <p class="ms-2">
                            المحافظ الإلكترونية
                            <i class="right fas fa-angle-left ms-2"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview pl-3">
                        <li class="nav-item">
                            <a href="{{ route('admin.wallets.index') }}" class="nav-link {{ request()->is('admin/wallets*') ? 'active' : '' }}">
                                <svg class="nav-icon" width="16" height="16" fill="#55efc4" viewBox="0 0 24 24">
                                    <rect x="2" y="7" width="20" height="10" rx="2"/>
                                    <circle cx="7" cy="12" r="2"/>
                                </svg>
                                <p class="ms-2">المحافظ</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.wallet_providers.index') }}" class="nav-link {{ request()->is('admin/wallet_providers*') ? 'active' : '' }}">
                                <svg class="nav-icon" width="16" height="16" fill="#81ecec" viewBox="0 0 24 24">
                                    <path d="M12 2a10 10 0 100 20 10 10 0 000-20zm1 14.5h-2v-2h2v2zm0-4h-2V7h2v5.5z"/>
                                </svg>
                                <p class="ms-2">مزودو المحافظ</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.wallet_transactions.index') }}" class="nav-link {{ request()->is('admin/wallet_transactions*') ? 'active' : '' }}">
                                <svg class="nav-icon" width="16" height="16" fill="#00cec9" viewBox="0 0 24 24">
                                    <path d="M12 8c-2.2 0-4 1.8-4 4s1.8 4 4 4 4-1.8 4-4-1.8-4-4-4zm0-6C6.5 2 2 6.5 2 12s4.5 10 10 10 10-4.5 10-10S17.5 2 12 2z"/>
                                </svg>
                                <p class="ms-2">حركات المحافظ</p>
                            </a>
                        </li>
                    </ul>
                </li>
                @endif

                <!-- التقارير -->
                @if(in_array('reports', $modules) || auth()->user()->role === 'super_admin')
                <li class="nav-item has-treeview {{ request()->is('admin/reports/*') ? 'menu-open' : '' }}">
                    <a href="#" class="nav-link {{ request()->is('admin/reports/*') ? 'active' : '' }}">
                        <svg class="nav-icon" width="20" height="20" fill="#0984e3" viewBox="0 0 24 24">
                            <path d="M3 13h6v8H3zm12-6h6v14h-6zM9 9h6v12H9z"/>
                        </svg>
                        <p class="ms-2">التقارير<i class="right fas fa-angle-left ms-2"></i></p>
                    </a>
                    <ul class="nav nav-treeview pl-3">
                        <li class="nav-item">
                            <a href="{{ route('admin.reports.sales') }}" class="nav-link {{ request()->routeIs('admin.reports.sales') ? 'active' : '' }}">
                                <svg class="nav-icon" width="16" height="16" fill="#6ab04c" viewBox="0 0 24 24">
                                    <path d="M3 17h18v2H3v-2zm0-4h12v2H3v-2zm0-4h6v2H3V9z"/>
                                </svg>
                                <p class="ms-2">تقرير المبيعات</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.reports.purchases') }}" class="nav-link {{ request()->routeIs('admin.reports.purchases') ? 'active' : '' }}">
                                <svg class="nav-icon" width="16" height="16" fill="#e17055" viewBox="0 0 24 24">
                                    <path d="M3 3h18v2H3V3zm0 4h12v2H3V7zm0 4h18v2H3v-2z"/>
                                </svg>
                                <p class="ms-2">تقرير المشتريات</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.reports.repairs') }}" class="nav-link {{ request()->routeIs('admin.reports.repairs') ? 'active' : '' }}">
                                <svg class="nav-icon" width="16" height="16" fill="#e17055" viewBox="0 0 24 24">
                                    <path d="M3 3h18v2H3V3zm0 4h12v2H3V7zm0 4h18v2H3v-2z"/>
                                </svg>
                                <p class="ms-2">تقرير الصيانه</p>
                            </a>
                        </li>
                    </ul>
                </li>
                @endif

                <!-- الإعدادات -->

                <li class="nav-item">
                    <a href="{{ route('admin.settings.edit') }}" class="nav-link {{ request()->is('admin/settings*') ? 'active' : '' }}">
                        <svg class="nav-icon" width="20" height="20" fill="#636e72" viewBox="0 0 24 24">
                            <path d="M19.4 15a7.95 7.95 0 0 0 .6-3 7.95 7.95 0 0 0-.6-3l2.1-1.6-2-3.5-2.5 1a8.2 8.2 0 0 0-2.6-1.5l-.4-2.6h-4l-.4 2.6A8.2 8.2 0 0 0 6 4.9l-2.5-1-2 3.5 2.1 1.6a7.95 7.95 0 0 0 0 6L1.5 16.6l2 3.5 2.5-1a8.2 8.2 0 0 0 2.6 1.5l.4 2.6h4l.4-2.6a8.2 8.2 0 0 0 2.6-1.5l2.5
                                1 2-3.5-2.1-1.6zM12 16c-2.2 0-4-1.8-4-4s1.8-4 4-4 4 1.8 4 4-1.8 4-4 4z"/>
                        </svg>
                        <p class="ms-2">الإعدادات</p>
                    </a>
                </li>
                <!-- الشركات -->
                @if(in_array('companies', $modules) || auth()->user()->role === 'super_admin')
                <li class="nav-item">
                    <a href="{{ route('admin.companies.index') }}" class="nav-link {{ request()->is('admin/companies*') ? 'active' : '' }}">
                        <svg class="nav-icon" width="20" height="20" fill="#dfe6e9" viewBox="0 0 24 24">
                            <path d="M3 3h18v18H3V3zm2 2v14h14V5H5zm4 2h6v2H9V7zm0 4h6v2H9v-2zm0 4h6v2H9v-2z"/>
                        </svg>
                        <p class="ms-2">الشركات</p>
                    </a>
                </li>
                @endif

            </ul>
        </nav>
    </div>
    <!-- /.sidebar -->
</aside>
