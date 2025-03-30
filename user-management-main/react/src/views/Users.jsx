import { useEffect, useState } from "react";
import axiosClient from "../axios-client.js";
import { Link } from "react-router-dom";
import { useStateContext } from "../context/ContextProvider.jsx";

export default function Users() {
  const [users, setUsers] = useState([]);
  const [loading, setLoading] = useState(false);
  const [searchTerm, setSearchTerm] = useState(""); // State for search term
  const { setNotification } = useStateContext()

  useEffect(() => {
    getUsers();
  }, [])

  const onDeleteClick = user => {
    if (!window.confirm("Are you sure you want to delete this user?")) {
      return;
    }
    axiosClient.delete(`/users/${user.id}`)
      .then((response) => {
        setNotification('User was successfully deleted');
        setUsers(users.filter((u) => u.id !== user.id));
        // getUsers();
      })
      .catch((error) =>{
        console.error("Error deleting user:", error); // Log the error for debugging
        setNotification("Failed to delete the user. Please try again.");
      })
  }

  const getUsers = (searchQuery= "") => {
    setLoading(true)
    // If there's a search query, pass it to the API

    axiosClient.get(`/users?search=${searchQuery}`)
      .then(({ data }) => {
        setLoading(false);
        setUsers(data.data);
        setSearchTerm(""); // Clear the search input after reload or fetch
      })
      .catch(() => {
        setLoading(false)
      })
  }


  // Function to handle search
  const handleSearch = () => {
    getUsers(searchTerm);
  };


  return (
    <div className="container mt-1">
      <div className="d-flex justify-content-between align-items-center mb-4">
        {/* Left section for search */}
        <div className="d-flex align-items-center w-75">
          <input
            type="text"
            className="form-control m-3 w-75" // Adjusted width to 75%
            placeholder="Search users..."
            value={searchTerm}
            onChange={(e) => setSearchTerm(e.target.value)}
          />
          <button onClick={handleSearch} className="btn btn-outline-primary">
            Search
          </button>
        </div>

        {/* Right section for 'Add new' button */}
        <div className="ml-3">
          <Link className="btn btn-outline-primary" to="/users/new">
            Add new
          </Link>
        </div>
      </div>

      <div className="card animated fadeInDown">
        <table className="table table-bordered table-striped">
          <thead className="thead-dark">
            <tr>
              <th>ID</th>
              <th>Name</th>
              <th>Email</th>
              <th>Create Date</th>
              <th>Actions</th>
            </tr>
          </thead>
          {loading && (
            <tbody>
              <tr>
                <td colSpan="5" className="text-center">
                  Loading...
                </td>
              </tr>
            </tbody>
          )}
          {!loading && (
            <tbody>
              {users.map((u) => (
                <tr key={u.id}>
                  <td>{u.id}</td>
                  <td>{u.name}</td>
                  <td>{u.email}</td>
                  <td>{u.created_at}</td>
                  <td>
                    <Link className="btn btn-warning btn-sm" to={"/users/" + u.id}>
                      Edit
                    </Link>
                    &nbsp;
                    <button
                      className="btn btn-danger btn-sm"
                      onClick={(ev) => onDeleteClick(u)}
                    >
                      Delete
                    </button>
                  </td>
                </tr>
              ))}
            </tbody>
          )}
        </table>
      </div>
    </div>
  );
}