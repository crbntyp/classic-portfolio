import { Routes, Route } from 'react-router-dom'
import Home from './components/Home'
import Login from './admin/Login'
import Dashboard from './admin/Dashboard'

export default function App() {
  return (
    <Routes>
      <Route path="/" element={<Home />} />
      <Route path="/admin" element={<Login />} />
      <Route path="/admin/dashboard" element={<Dashboard />} />
    </Routes>
  )
}
