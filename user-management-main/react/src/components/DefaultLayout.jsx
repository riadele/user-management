import {Link, Navigate, Outlet} from "react-router-dom";
import {useStateContext} from "../context/ContextProvider";
import axiosClient from "../axios-client.js";
import {useEffect} from "react";

export default function DefaultLayout() {
  const {user, token, setUser, setToken, notification} = useStateContext();

  if (!token) {
    return <Navigate to="/login"/>
  }

  const onLogout = ev => {
    ev.preventDefault()

    axiosClient.post('/logout')
      .then(() => {
        setUser({})
        setToken(null)
      })
  }

  useEffect(() => {
    axiosClient.get('/user')
      .then(({data}) => {
         setUser(data)
      })
  }, [])

  return (
    <div id="defaultLayout" className="container-fluid">
      <div className="content">
        <header className="d-flex justify-content-between align-items-center p-3 bg-light border-bottom">
          {/* Left: Home Link */}
          <div>
            <Link to="/users" className="btn btn-link text-decoration-none">
              Home
            </Link>
          </div>

          {/* Right: User info & Logout */}
          <div className="d-flex align-items-center">
            <span className="m-3">{user.name}</span>
            <button onClick={onLogout} className="btn btn-outline-danger btn-sm">
              Logout
            </button>
          </div>
        </header>

        {/* Main Content */}
        <main className="my-4">
          <Outlet />
        </main>

        {/* Notification */}
        {notification && (
          <div className="notification alert alert-info mt-3">
            {notification}
          </div>
        )}
      </div>
    </div>
  );
}
