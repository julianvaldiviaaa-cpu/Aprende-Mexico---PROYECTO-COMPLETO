import { useForm } from '@inertiajs/react';
import type { SyntheticEvent } from 'react';
import { store } from '@/actions/App/Http/Controllers/Identity/SessionController';
import AuthLayout, { authInputClassName } from '@/layouts/auth-layout';
import { Field } from '@/components/form-controls';

export default function Login() {
    const { data, setData, errors, clearErrors, submit, processing, reset } =
        useForm({ email: '', password: '', remember: false });
    function handleSubmit(event: SyntheticEvent) {
        event.preventDefault();
        submit(store(), {
            preserveScroll: true,
            onFinish: () => reset('password'),
        });
    }
    return (
        <AuthLayout>
            <h1 className="text-center text-3xl font-medium tracking-tight">
                Iniciar sesión
            </h1>
            <p className="mt-3 text-center text-sm text-white/60">
                Continúa donde te quedaste.
            </p>
            <form
                onSubmit={handleSubmit}
                className="mx-auto mt-10 flex w-full max-w-100 flex-col gap-5"
            >
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
                        autoComplete="current-password"
                        required
                        value={data.password}
                        onChange={(event) => {
                            setData('password', event.target.value);
                            clearErrors('password', 'email');
                        }}
                        className={authInputClassName}
                        placeholder="Tu contraseña"
                        aria-invalid={!!errors.password}
                        aria-describedby={
                            errors.password ? 'password-error' : undefined
                        }
                    />
                </Field>
                <label className="flex items-center gap-3 text-sm text-white/70">
                    <input
                        type="checkbox"
                        name="remember"
                        checked={data.remember}
                        onChange={(event) =>
                            setData('remember', event.target.checked)
                        }
                        className="size-4 accent-emerald-400"
                    />
                    Recordarme
                </label>
                <button
                    type="submit"
                    disabled={processing}
                    className="mt-3 rounded-lg bg-white py-3.5 text-lg font-semibold text-emerald-950 transition active:scale-[0.97] disabled:cursor-not-allowed disabled:opacity-50"
                >
                    {processing ? 'Iniciando sesión…' : 'Iniciar sesión'}
                </button>
            </form>
        </AuthLayout>
    );
}
