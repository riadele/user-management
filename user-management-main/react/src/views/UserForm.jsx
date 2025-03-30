import { useNavigate, useParams } from "react-router-dom";
import { useEffect, useState } from "react";
import axiosClient from "../axios-client.js";
import { useStateContext } from "../context/ContextProvider.jsx";

export default function UserForm() {
  const navigate = useNavigate();
  let { id } = useParams();
  const [user, setUser] = useState({
    id: null,
    name: '',
    email: '',
    password: '',
    password_confirmation: ''
  })
  const [errors, setErrors] = useState(null)
  const [loading, setLoading] = useState(false)
  const { setNotification } = useStateContext()

  if (id) {
    useEffect(() => {
      setLoading(true)
      axiosClient.get(`/users/${id}`)
        .then(({ data }) => {
          setLoading(false)
          setUser(data)
        })
        .catch(() => {
          setLoading(false)
        })
    }, [])
  }

  const onSubmit = ev => {
    ev.preventDefault()
    if (user.id) {
      axiosClient.put(`/users/${user.id}`, user)
        .then(() => {
          setNotification('User was successfully updated')
          navigate('/users')
        })
        .catch(err => {
          const response = err.response;
          if (response && response.status === 422) {
            setErrors(response.data.errors)
          }
        })
    } else {
      axiosClient.post('/users', user)
        .then(() => {
          setNotification('User was successfully created')
          navigate('/users')
        })
        .catch(err => {
          const response = err.response;
          if (response && response.status === 422) {
            setErrors(response.data.errors)
          }
        })
    }
  }


  const onCancel = () => {
    navigate('/users'); // This will navigate back to the home page
  }

  return (
    <>
      {user.id ? (
        <h1>Update User: {user.name}</h1>
      ) : (
        <h1>New User</h1>
      )}

      <div className="card animated fadeInDown">
        {loading && (
          <div className="text-center my-4">
            <div className="spinner-border text-primary" role="status">
              <span className="sr-only"></span>
            </div>
          </div>
        )}

        {errors && (
          <div className="alert alert-danger">
            {Object.keys(errors).map((key) => (
              <p key={key}>{errors[key][0]}</p>
            ))}
          </div>
        )}

        {!loading && (
          <form onSubmit={onSubmit} className="p-3">
            {/* Name input */}
            <div className="mb-3">
              <div className="form-floating">
                <input
                  type="text"
                  id="name"
                  className="form-control"
                  value={user.name}
                  onChange={(ev) =>
                    setUser({ ...user, name: ev.target.value })
                  }
                  placeholder="Enter name"
                  required
                />
                <label htmlFor="name">Name</label>
              </div>
            </div>

            {/* Email input */}
            <div className="mb-3">
              <div className="form-floating">
                <input
                  type="email"
                  id="email"
                  className="form-control"
                  value={user.email}
                  onChange={(ev) =>
                    setUser({ ...user, email: ev.target.value })
                  }
                  placeholder="Enter email"
                  required
                />
                <label htmlFor="email">Email</label>
              </div>
            </div>

            {/* Password input */}
            <div className="mb-3">
              <div className="form-floating">
                <input
                  type="password"
                  id="password"
                  className="form-control"
                  value={user.password}
                  onChange={(ev) =>
                    setUser({ ...user, password: ev.target.value })
                  }
                  placeholder="Enter password"
                  required
                />
                <label htmlFor="password">Password</label>
              </div>
            </div>

            {/* Password confirmation input */}
            <div className="mb-3">
              <div className="form-floating">
                <input
                  type="password"
                  id="password_confirmation"
                  className="form-control"
                  value={user.password_confirmation}
                  onChange={(ev) =>
                    setUser({ ...user, password_confirmation: ev.target.value })
                  }
                  placeholder="Confirm password"
                  required
                />
                <label htmlFor="password_confirmation">Confirm Password</label>
              </div>
            </div>

            {/* Submit button */}
            <div className="d-flex justify-content-end m-3">
              <div className="m-2">
                <button className="btn btn-primary" type="submit">
                  Save
                </button>
              </div>
              <div className="m-2">
                <button className="btn btn-secondary" type="button" onClick={onCancel}>
                  Cancel
                </button>
              </div>
            </div>
          </form>
        )}
      </div>

    </>
  );
}
