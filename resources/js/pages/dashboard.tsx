import { Link, usePage } from '@inertiajs/react';
import {
    create as apply,
    index as applications,
} from '@/actions/App/Http/Controllers/Identity/InstructorProfileController';
import {
    index as courses,
    create as createCourse,
} from '@/actions/App/Http/Controllers/Catalog/CourseController';
import { index as categories } from '@/actions/App/Http/Controllers/Catalog/CategoryController';
import AppLayout from '@/layouts/app-layout';

export default function Dashboard({
    courseCount,
    instructorProfile,
    pendingApplications,
    categoryCount,
}: {
    courseCount: number;
    instructorProfile: { status: string; display_name: string } | null;
    pendingApplications: number | null;
    categoryCount: number | null;
}) {
    const { auth } = usePage().props;
    const roleLabel =
        auth.user?.role === 'admin'
            ? 'Administrador'
            : auth.user?.role === 'instructor'
              ? 'Instructor'
              : 'Usuario';
    return (
        <AppLayout title="Mi espacio">
            <section className="mb-8 rounded-2xl bg-emerald-950 p-7 text-white sm:p-10">
                <p className="mb-3 text-xs font-medium tracking-wider text-emerald-200 uppercase">
                    {roleLabel}
                </p>
                <h2 className="text-3xl font-medium tracking-tight">
                    Hola, {auth.user?.name}.
                </h2>
                <p className="mt-4 max-w-xl leading-7 text-white/70">
                    Este es tu espacio para descubrir cursos y compartir
                    conocimiento. Elige por dónde quieres empezar.
                </p>
            </section>
            <div className="grid gap-5 md:grid-cols-2">
                <Link
                    href={courses()}
                    className="rounded-2xl border border-stone-200 bg-white p-7 hover:border-emerald-700"
                >
                    <h2 className="text-xl font-semibold">Explorar cursos →</h2>
                    <p className="mt-3 leading-6 text-stone-600">
                        Encuentra temas que te interesan y consulta los cursos
                        disponibles.
                    </p>
                </Link>
                {auth.can_create_courses && (
                    <Link
                        href={createCourse()}
                        className="rounded-2xl border border-stone-200 bg-white p-7 hover:border-emerald-700"
                    >
                        <h2 className="text-xl font-semibold">
                            Crear un curso →
                        </h2>
                        <p className="mt-3 leading-6 text-stone-600">
                            Tienes {courseCount} cursos propios. Prepara el
                            siguiente y elige sus categorías.
                        </p>
                    </Link>
                )}
                {auth.user?.role === 'user' && (
                    <Link
                        href={apply()}
                        className="rounded-2xl border border-stone-200 bg-white p-7 hover:border-emerald-700"
                    >
                        <h2 className="text-xl font-semibold">
                            Quiero ser instructor →
                        </h2>
                        <p className="mt-3 leading-6 text-stone-600">
                            {instructorProfile?.status === 'pending'
                                ? 'Tu solicitud está en revisión.'
                                : instructorProfile?.status === 'rejected'
                                  ? 'Tu solicitud fue rechazada. Puedes actualizarla y volver a enviarla.'
                                  : 'Presenta lo que quieres enseñar y solicita tu perfil de instructor.'}
                        </p>
                    </Link>
                )}
                {auth.is_admin && (
                    <>
                        <Link
                            href={applications()}
                            className="rounded-2xl border border-stone-200 bg-white p-7 hover:border-emerald-700"
                        >
                            <h2 className="text-xl font-semibold">
                                Revisar instructores →
                            </h2>
                            <p className="mt-3 leading-6 text-stone-600">
                                {pendingApplications} solicitudes pendientes de
                                aprobación.
                            </p>
                        </Link>
                        <Link
                            href={categories()}
                            className="rounded-2xl border border-stone-200 bg-white p-7 hover:border-emerald-700"
                        >
                            <h2 className="text-xl font-semibold">
                                Administrar categorías →
                            </h2>
                            <p className="mt-3 leading-6 text-stone-600">
                                {categoryCount} categorías disponibles. Crea
                                nuevas para organizar los cursos.
                            </p>
                        </Link>
                    </>
                )}
            </div>
        </AppLayout>
    );
}
