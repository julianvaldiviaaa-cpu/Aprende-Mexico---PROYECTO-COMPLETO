export type UserRole = 'user' | 'instructor' | 'admin';

export type User = {
    id: number;
    name: string;
    email: string;
    role: UserRole;
};

export type Auth = {
    user: User | null;
    can_create_courses: boolean;
    is_admin: boolean;
};
