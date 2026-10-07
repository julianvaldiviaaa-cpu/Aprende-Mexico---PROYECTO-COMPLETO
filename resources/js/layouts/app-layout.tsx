import { Head, Link, usePage } from '@inertiajs/react';
import type { ReactNode } from 'react';
import { dashboard, home, logout } from '@/routes';
import {
    index as courses,
    create as createCourse,
} from '@/actions/App/Http/Controllers/Catalog/CourseController';
import { index as categories } from '@/actions/App/Http/Controllers/Catalog/CategoryController';
import {
    create as apply,
    index as applications,
} from '@/actions/App/Http/Controllers/Identity/InstructorProfileController';

export default function AppLayout({
    title,
    children,
}: {
    title: string;
    children: ReactNode;
}) {
    const { auth, flash } = usePage().props;
    return (
        <div className="min-h-screen bg-[#f5f3ed] text-stone-900">
            <Head title={title} />
            <header className="border-b border-stone-200 bg-white">
                <div className="mx-auto flex max-w-6xl flex-wrap items-center justify-between gap-4 px-5 py-5">
                    <Link
                        href={home()}
                        className="text-lg font-bold tracking-tight"
                    >
                        México <span className="text-emerald-800">Aprende</span>
                    </Link>
                    <div className="flex items-center gap-4 text-sm">
                        <span>{auth.user?.name}</span>
                        <Link
                            href={logout()}
                            as="button"
                            className="rounded-lg border border-stone-300 px-3 py-2 hover:bg-stone-100"
                        >
                            Cerrar sesión
                        </Link>
                    </div>
                </div>
                <nav
                    aria-label="Navegación principal"
                    className="mx-auto flex max-w-6xl flex-wrap gap-x-6 gap-y-3 px-5 pb-5 text-sm font-medium"
                >
                    <Link href={dashboard()} className="hover:text-emerald-800">
                        Mi espacio
                    </Link>
                    <Link href={courses()} className="hover:text-emerald-800">
                        Cursos
                    </Link>
                    {auth.can_create_courses && (
                        <Link
                            href={createCourse()}
                            className="hover:text-emerald-800"
                        >
                            Crear curso
                        </Link>
                    )}
                    {auth.user?.role === 'user' && (
                        <Link href={apply()} className="hover:text-emerald-800">
                            Ser instructor
                        </Link>
                    )}
                    {auth.is_admin && (
                        <>
                            <Link
                                href={applications()}
                                className="hover:text-emerald-800"
                            >
                                Solicitudes de instructor
                            </Link>
                            <Link
                                href={categories()}
                                className="hover:text-emerald-800"
                            >
                                Categorías
                            </Link>
                        </>
                    )}
                </nav>
            </header>
            <main className="mx-auto max-w-6xl px-5 py-10">
                {flash?.success && (
                    <p
                        role="status"
                        className="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-emerald-900"
                    >
                        {flash.success}
                    </p>
                )}
                <h1 className="mb-8 text-3xl font-semibold tracking-tight">
                    {title}
                </h1>
                {children}
            </main>
        </div>
    );
}
