import { Link, usePage } from '@inertiajs/react';
import {
    create,
    edit,
} from '@/actions/App/Http/Controllers/Catalog/CourseController';
import AppLayout from '@/layouts/app-layout';
import { Pagination } from '@/components/form-controls';
import type { PageLink } from '@/components/form-controls';

type Course = {
    id: number;
    title: string;
    summary: string | null;
    status: string;
    categories: { id: number; name: string }[];
    instructor: { name: string };
    can_edit: boolean;
};
export default function Courses({
    courses,
}: {
    courses: { data: Course[]; links: PageLink[] };
}) {
    const { auth } = usePage().props;
    return (
        <AppLayout title="Cursos">
            <div className="mb-8 flex flex-wrap items-center justify-between gap-4">
                <p className="text-stone-600">
                    Explora los cursos publicados y administra los tuyos.
                </p>
                {auth.can_create_courses && (
                    <Link
                        href={create()}
                        className="rounded-xl bg-emerald-800 px-5 py-3 font-semibold text-white hover:bg-emerald-900"
                    >
                        Crear curso
                    </Link>
                )}
            </div>
            {courses.data.length === 0 && (
                <p className="rounded-2xl border border-dashed border-stone-300 p-12 text-center text-stone-600">
                    Todavía no hay cursos disponibles.
                </p>
            )}
            <div className="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                {courses.data.map((course) => (
                    <article
                        key={course.id}
                        className="flex flex-col rounded-2xl border border-stone-200 bg-white p-6"
                    >
                        <span className="mb-4 text-xs font-semibold tracking-wider text-emerald-800 uppercase">
                            {course.status === 'published'
                                ? 'Publicado'
                                : 'Borrador'}
                        </span>
                        <h2 className="text-xl font-semibold tracking-tight">
                            {course.title}
                        </h2>
                        <p className="mt-2 text-sm text-stone-500">
                            Por {course.instructor.name}
                        </p>
                        <p className="my-5 grow text-sm leading-6 text-stone-600">
                            {course.summary ||
                                'Este curso todavía no tiene un resumen.'}
                        </p>
                        <div className="mb-5 flex flex-wrap gap-2">
                            {course.categories.map((category) => (
                                <span
                                    key={category.id}
                                    className="rounded-full bg-stone-100 px-3 py-1 text-xs text-stone-700"
                                >
                                    {category.name}
                                </span>
                            ))}
                        </div>
                        {course.can_edit && (
                            <Link
                                href={edit(course.id)}
                                className="border-t border-stone-200 pt-4 text-sm font-semibold text-emerald-800 hover:underline"
                            >
                                Editar curso →
                            </Link>
                        )}
                    </article>
                ))}
            </div>
            <Pagination links={courses.links} />
        </AppLayout>
    );
}
