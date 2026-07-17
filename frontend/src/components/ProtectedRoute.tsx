import { Navigate } from 'react-router-dom';

type User = {
    id: number;
    email: string;
};

type ProtectedRouteProps = {
    user: User | null;
    children: React.ReactNode;
};

function ProtectedRoute({ user, children }: ProtectedRouteProps) {
    if (!user) {
        return <Navigate to="/login" replace />;
    }

    return children;
}

export default ProtectedRoute;