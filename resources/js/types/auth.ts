export type User = {
    id: number;
    name: string;
    email: string;
    avatar?: string;
    email_verified_at: string | null;
    two_factor_enabled?: boolean;
    created_at: string;
    updated_at: string;
    [key: string]: unknown;
};

export type Workspace = {
    id: number;
    name: string;
    slug: string;
    tier: string;
    settings: any;
    created_at: string;
    updated_at: string;
    is_suspended?: boolean;
};

export type Auth = {
    user: User;
    activeWorkspace?: Workspace | null;
    currentRole?: string | null;
    availableWorkspaces?: Workspace[];
    isGhostMode?: boolean;
    isImpersonating?: boolean;
};

export type Passkey = {
    id: number;
    name: string;
    authenticator: string | null;
    created_at_diff: string;
    last_used_at_diff: string | null;
};

export type TwoFactorConfigContent = {
    title: string;
    description: string;
    buttonText: string;
};
