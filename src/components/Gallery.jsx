import { useEffect, useRef, useState } from 'react'
import GalleryCard from './GalleryCard'
import './Gallery.css'

const API_URL = '/portfolio/api/artworks.php'

// Placeholder artworks for initial build — replaced by API data
const PLACEHOLDERS = [
  { id: 1, title: 'Blood Death Knight', category: 'artwork', image: 'https://placehold.co/420x240/1a0a0a/333?text=artwork+1', url: null },
  { id: 2, title: 'Havoc Demon Hunter', category: 'artwork', image: 'https://placehold.co/420x240/0a1a0a/333?text=artwork+2', url: null },
  { id: 3, title: 'Unholy Death Knight', category: 'artwork', image: 'https://placehold.co/420x240/0a0a1a/333?text=artwork+3', url: null },
  { id: 4, title: 'Guild App', category: 'project', image: 'https://placehold.co/420x240/1a1a0a/333?text=project+1', url: null },
  { id: 5, title: 'Drowsy Dragons', category: 'project', image: 'https://placehold.co/420x240/1a0a1a/333?text=project+2', url: null },
  { id: 6, title: 'Fantasy FPL', category: 'project', image: 'https://placehold.co/420x240/0a1a1a/333?text=project+3', url: null },
]

export default function Gallery() {
  const [artworks, setArtworks] = useState([])
  const trackRef = useRef(null)
  const speedRef = useRef(1)
  const posRef = useRef(0)
  const rafRef = useRef(null)

  useEffect(() => {
    fetch(API_URL)
      .then(res => res.ok ? res.json() : Promise.reject())
      .then(data => {
        if (data.length > 0) setArtworks(data)
        else setArtworks(PLACEHOLDERS)
      })
      .catch(() => setArtworks(PLACEHOLDERS))
  }, [])

  // Duplicate items for seamless infinite loop
  const items = artworks.length > 0
    ? [...artworks, ...artworks, ...artworks]
    : []

  useEffect(() => {
    const track = trackRef.current
    if (!track || artworks.length === 0) return

    const baseSpeed = 0.5
    const hoverSpeed = 0.08
    let targetSpeed = baseSpeed

    const animate = () => {
      // Smooth interpolation toward target speed
      speedRef.current += (targetSpeed - speedRef.current) * 0.03
      posRef.current -= speedRef.current

      // Reset position for seamless loop
      const singleSetWidth = track.scrollWidth / 3
      if (Math.abs(posRef.current) >= singleSetWidth) {
        posRef.current += singleSetWidth
      }

      track.style.transform = `translate3d(${posRef.current}px, 0, 0)`
      rafRef.current = requestAnimationFrame(animate)
    }

    const onEnter = () => { targetSpeed = hoverSpeed }
    const onLeave = () => { targetSpeed = baseSpeed }

    track.addEventListener('mouseenter', onEnter)
    track.addEventListener('mouseleave', onLeave)

    rafRef.current = requestAnimationFrame(animate)

    return () => {
      cancelAnimationFrame(rafRef.current)
      track.removeEventListener('mouseenter', onEnter)
      track.removeEventListener('mouseleave', onLeave)
    }
  }, [artworks])

  if (items.length === 0) return null

  return (
    <div className="gallery">
      <div className="gallery__track" ref={trackRef}>
        {items.map((artwork, i) => (
          <GalleryCard key={`${artwork.id}-${i}`} artwork={artwork} />
        ))}
      </div>
    </div>
  )
}
