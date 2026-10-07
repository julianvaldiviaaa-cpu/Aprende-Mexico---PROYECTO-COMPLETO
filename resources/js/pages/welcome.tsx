import { Head, Link, usePage } from '@inertiajs/react';
import { dashboard, login, register } from '@/routes';

export default function Welcome() {
    const { auth } = usePage().props;
    return (
        <div className="min-h-screen bg-[#f5f3ed] text-stone-900">
            <Head title="México Aprende" />
            <header className="mx-auto flex max-w-6xl items-center justify-between gap-4 px-6 py-7">
                <span className="text-xl font-bold tracking-tight">
                    México <span className="text-emerald-800">Aprende</span>
                </span>
                <nav className="flex items-center gap-4 text-sm font-medium">
                    {auth.user ? (
                        <Link
                            href={dashboard()}
                            className="rounded-lg bg-emerald-800 px-4 py-3 text-white"
                        >
                            Mi espacio
                        </Link>
                    ) : (
                        <>
                            <Link href={login()} className="hover:underline">
                                Iniciar sesión
                            </Link>
                            <Link
                                href={register()}
                                className="rounded-lg bg-emerald-800 px-4 py-3 text-white"
                            >
                                Crear cuenta
                            </Link>
                        </>
                    )}
                </nav>
            </header>
            <main className="mx-auto max-w-6xl px-6 py-12 sm:py-20">
                <section className="rounded-3xl bg-emerald-950 px-7 py-16 text-white sm:px-16 sm:py-24">
                    <p className="mb-7 text-xs tracking-[0.2em] text-emerald-200 uppercase">
                        Aprender nos acerca
                    </p>
                    <h1 className="max-w-3xl text-5xl leading-tight font-medium tracking-tight sm:text-7xl">
                        Lo que sabes puede abrir nuevos caminos.
                    </h1>
                    <p className="mt-8 max-w-xl text-lg leading-8 text-white/70">
                        Descubre cursos gratuitos, aprende a tu ritmo y comparte
                        tus conocimientos con una comunidad que quiere crecer.
                    </p>
                    <Link
                        href={auth.user ? dashboard() : register()}
                        className="mt-10 inline-flex rounded-lg bg-white px-7 py-4 font-semibold text-emerald-950"
                    >
                        {auth.user ? 'Ir a mi espacio' : 'Empieza a aprender'}{' '}
                        <span aria-hidden="true" className="ml-5">
                            →
                        </span>
                    </Link>
                </section>
                <div className="mt-10 grid gap-8 sm:grid-cols-3">
                    {[
                        ['Aprende', 'Descubre temas y avanza paso a paso.'],
                        [
                            'Comparte',
                            'Solicita tu perfil de instructor y crea tus cursos.',
                        ],
                        ['Crece', 'Haz del conocimiento una oportunidad.'],
                    ].map(([title, description]) => (
                        <div key={title}>
                            <h2 className="text-xl font-semibold">{title}</h2>
                            <p className="mt-3 leading-7 text-stone-600">
                                {description}
                            </p>
                        </div>
                    ))}
                </div>
            </main>
        </div>
    );
}
