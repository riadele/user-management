import { Link } from "react-router-dom";
import { createRef, useState } from "react";
import axiosClient from "../axios-client.js";
import { useStateContext } from "../context/ContextProvider.jsx";

export default function Signup() {
  const nameRef = createRef()
  const emailRef = createRef()
  const passwordRef = createRef()
  const passwordConfirmationRef = createRef()
  const { setUser, setToken } = useStateContext()
  const [errors, setErrors] = useState(null)

  const onSubmit = ev => {
    ev.preventDefault()

    const payload = {
      name: nameRef.current.value,
      email: emailRef.current.value,
      password: passwordRef.current.value,
      password_confirmation: passwordConfirmationRef.current.value,
    }
    axiosClient.post('/signup', payload)
      .then(({ data }) => {
        setUser(data.user)
        setToken(data.token);
      })
      .catch(err => {
        const response = err.response;
        if (response && response.status === 422) {
          setErrors(response.data.errors)
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
                <h1 className="title text-center mb-4">Signup for Free</h1>

                {errors && (
                  <div className="alert alert-danger" role="alert">
                    {Object.keys(errors).map(key => (
                      <p key={key}>{errors[key][0]}</p>
                    ))}
                  </div>
                )}

                <div className="mb-3">
                  <input
                    ref={nameRef}
                    type="text"
                    className="form-control"
                    placeholder="Full Name"
                    required
                  />
                </div>

                <div className="mb-3">
                  <input
                    ref={emailRef}
                    type="email"
                    className="form-control"
                    placeholder="Email Address"
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

                <div className="mb-3">
                  <input
                    ref={passwordConfirmationRef}
                    type="password"
                    className="form-control"
                    placeholder="Repeat Password"
                    required
                  />
                </div>

                <div className="mb-3 text-center">
                  <button className="btn btn-primary btn-block w-100" type="submit">
                    Signup
                  </button>
                </div>

                <p className="message text-center mt-3">
                  Already registered? <Link to="/login">Sign In</Link>
                </p>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>

  )
}
