import { useForm } from '@inertiajs/react';
import type { SyntheticEvent } from 'react';
import { store } from '@/actions/App/Http/Controllers/Identity/RegistrationController';
import AuthLayout, { authInputClassName } from '@/layouts/auth-layout';
import { Field } from '@/components/form-controls';

export default function Registration() {
    const { data, setData, errors, clearErrors, submit, processing, reset } =
        useForm({
            name: '',
            email: '',
            password: '',
            password_confirmation: '',
        });
    function handleSubmit(event: SyntheticEvent) {
        event.preventDefault();
        submit(store(), {
            preserveScroll: true,
            onFinish: () => reset('password', 'password_confirmation'),
        });
    }
    return (
        <AuthLayout registration>
            <h1 className="text-center text-3xl font-medium tracking-tight">
                Crear cuenta
            </h1>
            <p className="mt-3 text-center text-sm text-white/60">
                Tu cuenta estará lista al registrarte.
            </p>
            <form
                onSubmit={handleSubmit}
                className="mx-auto mt-8 flex w-full max-w-100 flex-col gap-5"
            >
                <Field id="name" label="Nombre completo" error={errors.name}>
                    <input
                        id="name"
                        name="name"
                        autoComplete="name"
                        required
                        maxLength={255}
                        value={data.name}
                        onChange={(event) => {
                            setData('name', event.target.value);
                            clearErrors('name');
                        }}
                        className={authInputClassName}
                        placeholder="Tu nombre"
                        aria-invalid={!!errors.name}
                        aria-describedby={
                            errors.name ? 'name-error' : undefined
                        }
                    />
                </Field>
                <Field
                    id="email"
                    label="Correo electrónico"
                    error={errors.email}
                >
                    <input
                        id="email"
                        name="email"
                        type="email"
                        autoComplete="email"
                        required
                        maxLength={255}
                        value={data.email}
                        onChange={(event) => {
                            setData('email', event.target.value);
                            clearErrors('email');
                        }}
                        className={authInputClassName}
                        placeholder="tu@correo.com"
                        aria-invalid={!!errors.email}
                        aria-describedby={
                            errors.email ? 'email-error' : undefined
                        }
                    />
                </Field>
                <Field id="password" label="Contraseña" error={errors.password}>
                    <input
                        id="password"
                        name="password"
                        type="password"
                        autoComplete="new-password"
                        required
                        minLength={8}
                        maxLength={72}
                        value={data.password}
                        onChange={(event) => {
                            setData('password', event.target.value);
                            clearErrors('password');
                        }}
                        className={authInputClassName}
                        placeholder="Mínimo 8 caracteres"
                        aria-invalid={!!errors.password}
                        aria-describedby="password-hint"
                    />
                    <p id="password-hint" className="text-xs text-white/60">
                        Incluye una letra, un número y un símbolo.
                    </p>
                </Field>
                <Field
                    id="password_confirmation"
                    label="Confirmar contraseña"
                    error={errors.password_confirmation}
                >
                    <input
                        id="password_confirmation"
                        name="password_confirmation"
                        type="password"
                        autoComplete="new-password"
                        required
                        value={data.password_confirmation}
                        onChange={(event) => {
                            setData(
                                'password_confirmation',
                                event.target.value,
                            );
                            clearErrors('password', 'password_confirmation');
                        }}
                        className={authInputClassName}
                        placeholder="Repite tu contraseña"
                    />
                </Field>
                <button
                    type="submit"
                    disabled={processing}
                    className="mt-2 rounded-lg bg-white py-3.5 text-lg font-semibold text-emerald-950 transition active:scale-[0.97] disabled:cursor-not-allowed disabled:opacity-50"
                >
                    {processing ? 'Creando cuenta…' : 'Crear cuenta'}
                </button>
            </form>
        </AuthLayout>
    );
}
