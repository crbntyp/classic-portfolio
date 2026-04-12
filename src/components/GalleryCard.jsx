import './GalleryCard.css'

export default function GalleryCard({ artwork }) {
  const imageUrl = artwork.image.startsWith('http')
    ? artwork.image
    : `/portfolio/uploads/${artwork.image}`

  const Card = artwork.url ? 'a' : 'div'
  const linkProps = artwork.url
    ? { href: artwork.url, target: '_blank', rel: 'noopener noreferrer' }
    : {}

  return (
    <Card className="gallery-card" {...linkProps}>
      <div className="gallery-card__image-wrap">
        <img
          src={imageUrl}
          alt={artwork.title}
          className="gallery-card__image"
          loading="lazy"
        />
      </div>
      <div className="gallery-card__overlay">
        <span className="gallery-card__title">{artwork.title}</span>
        {artwork.category && (
          <span className="gallery-card__category">{artwork.category}</span>
        )}
      </div>
    </Card>
  )
}
