import {Link} from "react-router-dom";
import axiosClient from "../axios-client.js";
import {createRef} from "react";
import {useStateContext} from "../context/ContextProvider.jsx";
import { useState } from "react";

export default function Login() {
  const emailRef = createRef()
  const passwordRef = createRef()
  const { setUser, setToken } = useStateContext()
  const [message, setMessage] = useState(null)

  const onSubmit = ev => {
    ev.preventDefault()

    const payload = {
      email: emailRef.current.value,
      password: passwordRef.current.value,
    }
    axiosClient.post('/login', payload)
      .then(({data}) => {
        setUser(data.user)
        setToken(data.token);
      })
      .catch((err) => {
        const response = err.response;
        if (response && response.status === 422) {
          setMessage(response.data.message)
        }
      })
  }

  return (
    <div className="login-signup-form animated fadeInDown">
  <div className="container">
    <div className="row justify-content-center">
      <div className="col-md-6 col-lg-4">
        <div className="form p-4 shadow-lg rounded">
          <form onSubmit={onSubmit}>
            <h1 className="title text-center mb-4">Login into your account</h1>

            {message && (
              <div className="alert alert-danger" role="alert">
                <p>{message}</p>
              </div>
            )}

            <div className="mb-3">
              <input
                ref={emailRef}
                type="email"
                className="form-control"
                placeholder="Email"
                required
              />
            </div>

            <div className="mb-3">
              <input
                ref={passwordRef}
                type="password"
                className="form-control"
                placeholder="Password"
                required
              />
            </div>

            <div className="mb-3 text-center">
              <button className="btn btn-primary btn-block w-100" type="submit">
                Login
              </button>
            </div>

            <p className="message text-center mt-3">
              Not registered? <Link to="/signup">Create an account</Link>
            </p>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>

  )
}
