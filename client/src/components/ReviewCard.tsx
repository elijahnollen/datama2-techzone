import { useState } from 'react';
import { X, Star } from 'lucide-react';

interface ReviewCardProps {
  productName: string;
  productImage: string;
  onClose: () => void;
  onSubmit: (rating: number, review: string) => void;
}

export function ReviewCard({ productName, productImage, onClose, onSubmit }: ReviewCardProps) {
  const [rating, setRating] = useState(0);
  const [hoveredRating, setHoveredRating] = useState(0);
  const [review, setReview] = useState('');
  const maxChars = 1000;

  const handleSubmit = () => {
    if (rating > 0 && review.trim()) {
      onSubmit(rating, review);
      onClose();
    }
  };

  const isSubmitDisabled = rating === 0 || review.trim().length === 0;

  return (
    <div className="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
      <div className="bg-white rounded-2xl shadow-2xl max-w-[512px] w-full p-8 relative">
        {/* Close Button */}
        <button
          onClick={onClose}
          className="absolute top-6 right-6 text-zinc-400 hover:text-zinc-600 transition-colors"
        >
          <X className="w-6 h-6" />
        </button>

        {/* Header */}
        <h3 className="text-[24px] font-bold italic mb-2">
          <span className="text-black">WRITE </span>
          <span className="text-cyan-500">REVIEW</span>
        </h3>
        <p className="font-bold text-zinc-500 text-xs uppercase tracking-wider mb-6">
          Share your experience with other people
        </p>

        {/* Product Info */}
        <div className="bg-zinc-50 border border-zinc-100 rounded-xl p-4 flex items-center gap-4 mb-6">
          <div className="bg-white border border-zinc-200 rounded-lg w-12 h-12 flex items-center justify-center p-1">
            <img
              src={productImage}
              alt={productName}
              className="w-full h-full object-contain"
            />
          </div>
          <div>
            <p className="font-bold text-zinc-400 text-[10px] uppercase tracking-wider mb-1">
              Product
            </p>
            <p className="font-bold text-sm text-black">{productName}</p>
          </div>
        </div>

        {/* Overall Rating */}
        <div className="mb-6">
          <label className="block font-bold text-zinc-500 text-[10px] uppercase tracking-wider mb-3">
            Overall Rating
          </label>
          <div className="flex gap-2">
            {[1, 2, 3, 4, 5].map((star) => (
              <button
                key={star}
                type="button"
                onClick={() => setRating(star)}
                onMouseEnter={() => setHoveredRating(star)}
                onMouseLeave={() => setHoveredRating(0)}
                className="transition-transform hover:scale-110"
              >
                <Star
                  className={`w-8 h-8 transition-colors ${
                    star <= (hoveredRating || rating)
                      ? 'fill-cyan-500 text-cyan-500'
                      : 'fill-zinc-300 text-zinc-300'
                  }`}
                />
              </button>
            ))}
          </div>
        </div>

        {/* Comment Box */}
        <div className="mb-6">
          <div className="flex items-center justify-between mb-2">
            <label className="font-bold text-zinc-500 text-[10px] uppercase tracking-wider">
              Your Review
            </label>
            <span className="font-bold text-zinc-400 text-[8px]">
              {review.length}/{maxChars}
            </span>
          </div>
          <textarea
            value={review}
            onChange={(e) => {
              if (e.target.value.length <= maxChars) {
                setReview(e.target.value);
              }
            }}
            placeholder="How is the performance?"
            className="w-full min-h-[120px] bg-zinc-50 border border-zinc-200 rounded-xl p-4 text-sm text-black placeholder:text-zinc-400 focus:outline-none focus:ring-2 focus:ring-cyan-500 resize-none"
          />
        </div>

        {/* Submit Button */}
        <button
          onClick={handleSubmit}
          disabled={isSubmitDisabled}
          className="w-full bg-cyan-500 text-black font-bold text-xs uppercase tracking-wider py-4 rounded-xl shadow-lg hover:bg-cyan-600 transition-colors disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:bg-cyan-500"
        >
          Submit Review
        </button>
      </div>
    </div>
  );
}
