import { Navigate } from 'react-router-dom';

type User = {
    id: number;
    email: string;
};

type GuestRouteProps = {
    user: User | null;
    children: React.ReactNode;
};

function GuestRoute({ user, children }: GuestRouteProps) {
    if (user) {
        return <Navigate to="/profile" replace />;
    }

    return children;
}

export default GuestRoute;