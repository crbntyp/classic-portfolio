import { useState, useEffect, useRef } from 'react'
import { useNavigate } from 'react-router-dom'
import './Admin.css'

export default function Dashboard() {
  const [artworks, setArtworks] = useState([])
  const [loading, setLoading] = useState(true)
  const [showForm, setShowForm] = useState(false)
  const [editing, setEditing] = useState(null)
  const [formData, setFormData] = useState({ title: '', category: 'artwork', url: '', description: '' })
  const [file, setFile] = useState(null)
  const [preview, setPreview] = useState(null)
  const [saving, setSaving] = useState(false)
  const fileRef = useRef()
  const dragItem = useRef(null)
  const dragOver = useRef(null)
  const navigate = useNavigate()

  const fetchArtworks = async () => {
    try {
      const res = await fetch('/portfolio/api/artworks.php', { credentials: 'same-origin' })
      const data = await res.json()
      setArtworks(data)
    } catch {
      console.error('Failed to fetch artworks')
    } finally {
      setLoading(false)
    }
  }

  useEffect(() => {
    // Check auth
    fetch('/portfolio/api/artworks.php?auth_check=1', { credentials: 'same-origin' })
      .then(res => { if (res.status === 401) navigate('/admin') })
    fetchArtworks()
  }, [navigate])

  const resetForm = () => {
    setFormData({ title: '', category: 'artwork', url: '', description: '' })
    setFile(null)
    setPreview(null)
    setEditing(null)
    setShowForm(false)
    if (fileRef.current) fileRef.current.value = ''
  }

  const handleEdit = (artwork) => {
    setEditing(artwork.id)
    setFormData({
      title: artwork.title,
      category: artwork.category,
      url: artwork.url || '',
      description: artwork.description || ''
    })
    setPreview(artwork.image.startsWith('http') ? artwork.image : `/portfolio/uploads/${artwork.image}`)
    setShowForm(true)
  }

  const handleFileChange = (e) => {
    const f = e.target.files[0]
    if (!f) return
    setFile(f)
    setPreview(URL.createObjectURL(f))
  }

  const handleSubmit = async (e) => {
    e.preventDefault()
    setSaving(true)

    try {
      let imageName = editing ? artworks.find(a => a.id === editing)?.image : null

      // Upload image if new file selected
      if (file) {
        const uploadData = new FormData()
        uploadData.append('image', file)
        const uploadRes = await fetch('/portfolio/api/upload.php', {
          method: 'POST',
          credentials: 'same-origin',
          body: uploadData
        })
        const uploadResult = await uploadRes.json()
        if (!uploadResult.success) throw new Error(uploadResult.message)
        imageName = uploadResult.filename
      }

      if (!imageName && !editing) {
        alert('Please select an image')
        setSaving(false)
        return
      }

      const payload = { ...formData, image: imageName }
      const method = editing ? 'PUT' : 'POST'
      const url = editing ? `/api/artworks.php?id=${editing}` : '/portfolio/api/artworks.php'

      const res = await fetch(url, {
        method,
        headers: { 'Content-Type': 'application/json' },
        credentials: 'same-origin',
        body: JSON.stringify(payload)
      })

      const result = await res.json()
      if (!result.success) throw new Error(result.message)

      resetForm()
      fetchArtworks()
    } catch (err) {
      alert(err.message || 'Save failed')
    } finally {
      setSaving(false)
    }
  }

  const handleDelete = async (id) => {
    if (!confirm('Delete this artwork?')) return

    try {
      await fetch(`/api/artworks.php?id=${id}`, {
        method: 'DELETE',
        credentials: 'same-origin'
      })
      fetchArtworks()
    } catch {
      alert('Delete failed')
    }
  }

  const handleDragStart = (index) => { dragItem.current = index }
  const handleDragEnter = (index) => { dragOver.current = index }

  const handleDragEnd = async () => {
    const reordered = [...artworks]
    const [removed] = reordered.splice(dragItem.current, 1)
    reordered.splice(dragOver.current, 0, removed)
    setArtworks(reordered)

    const order = reordered.map((a, i) => ({ id: a.id, sort_order: i }))
    await fetch('/portfolio/api/reorder.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      credentials: 'same-origin',
      body: JSON.stringify({ order })
    })

    dragItem.current = null
    dragOver.current = null
  }

  const handleLogout = async () => {
    await fetch('/portfolio/api/login.php?logout=1', { credentials: 'same-origin' })
    navigate('/admin')
  }

  return (
    <div className="admin-dash">
      <header className="admin-dash__header">
        <h1 className="admin-dash__logo">crbntyp</h1>
        <div className="admin-dash__actions">
          <button className="admin-btn" onClick={() => { resetForm(); setShowForm(!showForm) }}>
            {showForm ? 'Cancel' : '+ Add'}
          </button>
          <button className="admin-btn admin-btn--ghost" onClick={handleLogout}>
            Logout
          </button>
        </div>
      </header>

      {showForm && (
        <form className="admin-form" onSubmit={handleSubmit}>
          <div className="admin-form__row">
            <div className="admin-form__field">
              <label>Title</label>
              <input
                type="text"
                value={formData.title}
                onChange={e => setFormData({ ...formData, title: e.target.value })}
                required
              />
            </div>
            <div className="admin-form__field">
              <label>Category</label>
              <select
                value={formData.category}
                onChange={e => setFormData({ ...formData, category: e.target.value })}
              >
                <option value="artwork">Artwork</option>
                <option value="project">Project</option>
              </select>
            </div>
          </div>
          <div className="admin-form__field">
            <label>URL (optional)</label>
            <input
              type="url"
              value={formData.url}
              onChange={e => setFormData({ ...formData, url: e.target.value })}
              placeholder="https://..."
            />
          </div>
          <div className="admin-form__field">
            <label>Description (optional)</label>
            <textarea
              value={formData.description}
              onChange={e => setFormData({ ...formData, description: e.target.value })}
              rows={3}
            />
          </div>
          <div className="admin-form__field">
            <label>Image</label>
            <input
              ref={fileRef}
              type="file"
              accept="image/*"
              onChange={handleFileChange}
            />
            {preview && (
              <img src={preview} alt="Preview" className="admin-form__preview" />
            )}
          </div>
          <button type="submit" className="admin-btn" disabled={saving}>
            {saving ? 'Saving...' : editing ? 'Update' : 'Add Artwork'}
          </button>
        </form>
      )}

      {loading ? (
        <p className="admin-dash__loading">Loading...</p>
      ) : (
        <div className="admin-grid">
          {artworks.map((artwork, index) => (
            <div
              key={artwork.id}
              className="admin-card"
              draggable
              onDragStart={() => handleDragStart(index)}
              onDragEnter={() => handleDragEnter(index)}
              onDragEnd={handleDragEnd}
              onDragOver={e => e.preventDefault()}
            >
              <img
                src={artwork.image.startsWith('http') ? artwork.image : `/portfolio/uploads/${artwork.image}`}
                alt={artwork.title}
                className="admin-card__image"
              />
              <div className="admin-card__info">
                <span className="admin-card__title">{artwork.title}</span>
                <span className="admin-card__category">{artwork.category}</span>
              </div>
              <div className="admin-card__controls">
                <button onClick={() => handleEdit(artwork)} className="admin-btn admin-btn--sm">Edit</button>
                <button onClick={() => handleDelete(artwork.id)} className="admin-btn admin-btn--sm admin-btn--danger">Delete</button>
              </div>
            </div>
          ))}
        </div>
      )}
    </div>
  )
}
