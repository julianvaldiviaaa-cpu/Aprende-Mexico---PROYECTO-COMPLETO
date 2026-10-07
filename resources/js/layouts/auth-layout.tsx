import { Head, Link } from '@inertiajs/react';
import type { ReactNode } from 'react';
import { home, login, register } from '@/routes';

export const authInputClassName =
    'w-full rounded-lg border border-white/15 bg-white/5 px-4 py-3 text-white outline-none transition placeholder:text-white/30 focus:border-white focus:ring-2 focus:ring-white/30';

export default function AuthLayout({
    children,
    registration = false,
}: {
    children: ReactNode;
    registration?: boolean;
}) {
    return (
        <div className="min-h-screen bg-[#f5f3ed] px-4 py-6 text-stone-900 sm:px-6">
            <Head title={registration ? 'Crear cuenta' : 'Iniciar sesión'} />
            <header className="mx-auto flex max-w-5xl items-center justify-between">
                <Link
                    href={home()}
                    className="text-lg font-bold tracking-tight"
                >
                    México <span className="text-emerald-800">Aprende</span>
                </Link>
                <Link href={home()} className="text-sm hover:underline">
                    Volver al inicio
                </Link>
            </header>
            <main className="mx-auto my-8 max-w-5xl sm:my-12">
                <div className="grid grid-cols-1 gap-2 rounded-3xl bg-[#111713] p-2 md:min-h-[640px] md:grid-cols-2">
                    <div className="relative hidden overflow-hidden rounded-2xl bg-emerald-950 px-10 py-12 text-white md:flex md:flex-col md:justify-between">
                        <svg
                            viewBox="0 0 320 180"
                            fill="none"
                            aria-hidden="true"
                            className="mx-auto w-full max-w-sm text-emerald-300/70"
                        >
                            <circle
                                cx="160"
                                cy="90"
                                r="82"
                                stroke="currentColor"
                                strokeDasharray="3 8"
                            />
                            <path
                                d="M160 55C130 36 96 36 60 45V130C96 121 130 121 160 140C190 121 224 121 260 130V45C224 36 190 36 160 55Z"
                                stroke="currentColor"
                                strokeWidth="3"
                            />
                            <path
                                d="M160 55V140M85 68L136 72M85 89L136 93M184 72L235 68M184 93L235 89"
                                stroke="currentColor"
                                strokeWidth="3"
                            />
                        </svg>
                        <div className="mx-auto w-full max-w-xs text-center">
                            <p className="mb-4 text-xs tracking-[0.2em] text-emerald-200 uppercase">
                                Un espacio para aprender
                            </p>
                            <h2 className="text-4xl leading-tight tracking-tight">
                                {registration
                                    ? 'Tu próximo paso empieza aquí.'
                                    : 'Bienvenido de vuelta.'}
                            </h2>
                            <p className="my-5 text-sm leading-6 text-white/70">
                                Aprende a tu ritmo, descubre nuevos temas y
                                comparte lo que sabes con México Aprende.
                            </p>
                            <div className="mx-auto h-px w-60 bg-white/20" />
                            <Link
                                href={registration ? login() : register()}
                                className="my-7 flex w-full items-center justify-center gap-3 rounded-lg bg-white px-5 py-4 text-lg font-semibold text-emerald-950 transition active:scale-[0.97]"
                            >
                                {registration
                                    ? 'Iniciar sesión'
                                    : 'Crear cuenta'}{' '}
                                <span aria-hidden="true">→</span>
                            </Link>
                        </div>
                        <p className="text-center text-xs text-white/50">
                            El conocimiento crece cuando se comparte.
                        </p>
                    </div>
                    <div className="flex flex-col justify-center px-6 py-10 text-white sm:px-10">
                        {children}
                        <Link
                            href={registration ? login() : register()}
                            className="mt-7 text-center text-sm text-white/70 hover:text-white md:hidden"
                        >
                            {registration
                                ? '¿Ya tienes cuenta? Inicia sesión'
                                : '¿No tienes cuenta? Regístrate'}
                        </Link>
                    </div>
                </div>
            </main>
        </div>
    );
}
