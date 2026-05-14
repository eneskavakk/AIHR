<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Dashboard;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets\AccountWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->brandName('AIHR Platform')
            ->darkMode(true, true)
            ->sidebarCollapsibleOnDesktop()
            ->colors([
                'primary' => Color::Violet,
                'danger' => Color::Rose,
                'gray' => Color::Slate,
                'info' => Color::Sky,
                'success' => Color::Emerald,
                'warning' => Color::Amber,
            ])
            ->font('Inter')
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                AccountWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->renderHook('panels::head.end', fn (): string => '<style>
                /* Sidebar gradient background */
                .fi-sidebar {
                    background: linear-gradient(180deg, rgb(15 23 42) 0%, rgb(30 27 75) 50%, rgb(15 23 42) 100%) !important;
                    border-right: 1px solid rgba(139, 92, 246, 0.15) !important;
                }
                .fi-sidebar .fi-sidebar-nav {
                    background: transparent !important;
                }

                /* Sidebar items hover glow */
                .fi-sidebar-item a {
                    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
                    border-radius: 0.75rem !important;
                }
                .fi-sidebar-item a:hover {
                    background: rgba(139, 92, 246, 0.12) !important;
                    transform: translateX(4px);
                    box-shadow: 0 0 20px rgba(139, 92, 246, 0.08);
                }
                .fi-sidebar-item-active a {
                    background: linear-gradient(135deg, rgba(139, 92, 246, 0.2), rgba(99, 102, 241, 0.15)) !important;
                    border-left: 3px solid rgb(139, 92, 246) !important;
                    box-shadow: 0 4px 15px rgba(139, 92, 246, 0.12);
                }

                /* Sidebar group labels */
                .fi-sidebar-group-label {
                    text-transform: uppercase;
                    font-size: 0.65rem !important;
                    letter-spacing: 0.1em;
                    opacity: 0.5;
                }

                /* Card glassmorphism */
                .fi-section, .fi-wi-stats-overview-stat {
                    backdrop-filter: blur(12px);
                    border: 1px solid rgba(139, 92, 246, 0.08) !important;
                    transition: all 0.3s ease !important;
                }
                .fi-section:hover {
                    border-color: rgba(139, 92, 246, 0.18) !important;
                    box-shadow: 0 8px 32px rgba(139, 92, 246, 0.06);
                }

                /* Stat widget animation */
                .fi-wi-stats-overview-stat {
                    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1) !important;
                }
                .fi-wi-stats-overview-stat:hover {
                    transform: translateY(-2px);
                    box-shadow: 0 12px 40px rgba(139, 92, 246, 0.1);
                    border-color: rgba(139, 92, 246, 0.25) !important;
                }

                /* Badge pulse for processing status */
                @keyframes pulse-glow {
                    0%, 100% { box-shadow: 0 0 0 0 rgba(59, 130, 246, 0.4); }
                    50% { box-shadow: 0 0 0 6px rgba(59, 130, 246, 0); }
                }

                /* Table row hover */
                .fi-ta-row {
                    transition: all 0.2s ease !important;
                }
                .fi-ta-row:hover {
                    background: rgba(139, 92, 246, 0.04) !important;
                }

                /* Button glow effects */
                .fi-btn-primary {
                    transition: all 0.3s ease !important;
                }
                .fi-btn-primary:hover {
                    box-shadow: 0 4px 20px rgba(139, 92, 246, 0.3);
                }

                /* Topbar subtle gradient */
                .fi-topbar {
                    backdrop-filter: blur(16px);
                    border-bottom: 1px solid rgba(139, 92, 246, 0.08) !important;
                }

                /* Smooth page transitions */
                .fi-main {
                    animation: fadeIn 0.3s ease-out;
                }
                @keyframes fadeIn {
                    from { opacity: 0; transform: translateY(8px); }
                    to { opacity: 1; transform: translateY(0); }
                }

                /* Modal backdrop blur */
                .fi-modal-window {
                    backdrop-filter: blur(8px);
                    border: 1px solid rgba(139, 92, 246, 0.15) !important;
                }

                /* Scrollbar styling */
                ::-webkit-scrollbar { width: 6px; }
                ::-webkit-scrollbar-track { background: transparent; }
                ::-webkit-scrollbar-thumb { background: rgba(139, 92, 246, 0.2); border-radius: 3px; }
                ::-webkit-scrollbar-thumb:hover { background: rgba(139, 92, 246, 0.4); }
            </style>');
    }
}
