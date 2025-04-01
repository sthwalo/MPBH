import { useState, useEffect } from 'react'
import { Link } from 'react-router-dom'

function ManageProducts({ businessData }) {
  const [products, setProducts] = useState([])
  const [loading, setLoading] = useState(true)
  const [formVisible, setFormVisible] = useState(false)
  const [editingProduct, setEditingProduct] = useState(null)
  const [formData, setFormData] = useState({
    name: '',
    price: '',
    description: ''
  })
  const [errors, setErrors] = useState({})
  const [successMessage, setSuccessMessage] = useState('')

  // Check if current plan allows products feature
  const canManageProducts = businessData.package_type === 'Silver' || businessData.package_type === 'Gold'

  useEffect(() => {
    // In production, this would fetch the products from the API
    // const fetchProducts = async () => {
    //   try {
    //     const response = await fetch('/api/products', {
    //       headers: {
    //         'Authorization': `Bearer ${localStorage.getItem('token')}`
    //       }
    //     })
    //     if (!response.ok) throw new Error('Failed to fetch products')
    //     const data = await response.json()
    //     setProducts(data)
    //   } catch (error) {
    //     console.error('Error fetching products:', error)
    //   } finally {
    //     setLoading(false)
    //   }
    // }
    // 
    // fetchProducts()
    
    // For demo, we'll use mock data
    setTimeout(() => {
      setProducts([
        {
          id: 1,
          name: 'Safari Day Trip',
          price: 1200,
          description: 'Full day game drive in Kruger National Park with lunch included.',
          image: '/assets/images/product-1.jpg'
        },
        {
          id: 2,
          name: 'Panorama Route Tour',
          price: 950,
          description: 'Guided tour of Blyde River Canyon, God\'s Window, and waterfalls.',
          image: '/assets/images/product-2.jpg'
        },
        {
          id: 3,
          name: 'Bush Dinner Experience',
          price: 650,
          description: 'Evening dinner in the bush with traditional music and dancing.',
          image: '/assets/images/product-3.jpg'
        }
      ])
      setLoading(false)
    }, 1000)
  }, [])

  const resetForm = () => {
    setFormData({
      name: '',
      price: '',
      description: ''
    })
    setErrors({})
    setSuccessMessage('')
    setEditingProduct(null)
  }

  const handleShowForm = () => {
    resetForm()
    setFormVisible(true)
  }

  const handleChange = (e) => {
    const { name, value } = e.target
    setFormData(prevData => ({
      ...prevData,
      [name]: value
    }))
    
    // Clear error when user starts typing
    if (errors[name]) {
      setErrors(prev => ({ ...prev, [name]: '' }))
    }
  }

  const validateForm = () => {
    const newErrors = {}
    
    if (!formData.name.trim()) {
      newErrors.name = 'Product name is required'
    }
    
    if (!formData.price) {
      newErrors.price = 'Price is required'
    } else if (isNaN(parseFloat(formData.price)) || parseFloat(formData.price) <= 0) {
      newErrors.price = 'Price must be a positive number'
    }
    
    if (!formData.description.trim()) {
      newErrors.description = 'Description is required'
    }
    
    setErrors(newErrors)
    return Object.keys(newErrors).length === 0
  }

  const handleSubmit = async (e) => {
    e.preventDefault()
    
    if (!validateForm()) return
    
    // In production, this would be a real API call
    // const url = editingProduct ? `/api/products/${editingProduct.id}` : '/api/products'
    // const method = editingProduct ? 'PUT' : 'POST'
    // 
    // try {
    //   const response = await fetch(url, {
    //     method,
    //     headers: {
    //       'Content-Type': 'application/json',
    //       'Authorization': `Bearer ${localStorage.getItem('token')}`
    //     },
    //     body: JSON.stringify(formData)
    //   })
    //   
    //   if (!response.ok) throw new Error('Failed to save product')
    //   const data = await response.json()
    //   
    //   if (editingProduct) {
    //     setProducts(products.map(p => p.id === editingProduct.id ? data : p))
    //   } else {
    //     setProducts([...products, data])
    //   }
    //   
    //   setSuccessMessage(`Product ${editingProduct ? 'updated' : 'added'} successfully!`)
    //   resetForm()
    //   setFormVisible(false)
    // } catch (error) {
    //   setErrors({ form: error.message || 'Failed to save product' })
    // }
    
    // For demo purposes, we'll just simulate adding/updating a product
    setTimeout(() => {
      if (editingProduct) {
        setProducts(products.map(p => p.id === editingProduct.id ? {
          ...p,
          name: formData.name,
          price: parseFloat(formData.price),
          description: formData.description
        } : p))
        setSuccessMessage('Product updated successfully!')
      } else {
        const newProduct = {
          id: products.length + 1,
          name: formData.name,
          price: parseFloat(formData.price),
          description: formData.description,
          image: '/assets/images/product-placeholder.jpg'
        }
        setProducts([...products, newProduct])
        setSuccessMessage('Product added successfully!')
      }
      resetForm()
      setFormVisible(false)
    }, 500)
  }

  const handleEdit = (product) => {
    setFormData({
      name: product.name,
      price: product.price.toString(),
      description: product.description
    })
    setEditingProduct(product)
    setFormVisible(true)
    window.scrollTo(0, 0)
  }

  const handleDelete = async (productId) => {
    if (!window.confirm('Are you sure you want to delete this product?')) return
    
    // In production, this would be a real API call
    // try {
    //   const response = await fetch(`/api/products/${productId}`, {
    //     method: 'DELETE',
    //     headers: {
    //       'Authorization': `Bearer ${localStorage.getItem('token')}`
    //     }
    //   })
    //   
    //   if (!response.ok) throw new Error('Failed to delete product')
    //   
    //   setProducts(products.filter(p => p.id !== productId))
    //   setSuccessMessage('Product deleted successfully!')
    // } catch (error) {
    //   setErrors({ form: error.message || 'Failed to delete product' })
    // }
    
    // For demo purposes, we'll just simulate deleting a product
    setProducts(products.filter(p => p.id !== productId))
    setSuccessMessage('Product deleted successfully!')
  }

  if (!canManageProducts) {
    return (
      <div className="text-center py-10">
        <svg className="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
        </svg>
        <h3 className="mt-2 text-lg font-medium text-gray-900">Feature not available</h3>
        <p className="mt-1 text-gray-500">Product management is available for Silver and Gold packages only.</p>
        <div className="mt-6">
          <Link to="/dashboard/upgrade" className="btn btn-primary">
            Upgrade Your Package
          </Link>
        </div>
      </div>
    )
  }

  return (
    <div>
      <div className="flex justify-between items-center mb-6">
        <h2 className="text-2xl font-bold">Manage Products & Services</h2>
        {!formVisible && (
          <button 
            onClick={handleShowForm}
            className="btn btn-primary flex items-center"
          >
            <svg className="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
            </svg>
            Add New Product
          </button>
        )}
      </div>

      {/* Success message */}
      {successMessage && (
        <div className="bg-green-50 border border-green-200 text-green-800 rounded-md p-4 mb-6">
          {successMessage}
        </div>
      )}
      
      {/* Error message */}
      {errors.form && (
        <div className="bg-red-50 border border-red-200 text-red-800 rounded-md p-4 mb-6">
          {errors.form}
        </div>
      )}

      {/* Add/Edit Product Form */}
      {formVisible && (
        <div className="bg-white border rounded-lg p-6 mb-6">
          <h3 className="text-lg font-bold mb-4">{editingProduct ? 'Edit Product' : 'Add New Product'}</h3>
          <form onSubmit={handleSubmit}>
            <div className="mb-4">
              <label htmlFor="name" className="form-label">Product Name</label>
              <input
                type="text"
                id="name"
                name="name"
                className={`form-control ${errors.name ? 'border-red-500' : ''}`}
                value={formData.name}
                onChange={handleChange}
                placeholder="e.g. Safari Day Trip"
              />
              {errors.name && <p className="text-red-500 text-sm mt-1">{errors.name}</p>}
            </div>
            
            <div className="mb-4">
              <label htmlFor="price" className="form-label">Price (R)</label>
              <input
                type="number"
                id="price"
                name="price"
                step="0.01"
                min="0"
                className={`form-control ${errors.price ? 'border-red-500' : ''}`}
                value={formData.price}
                onChange={handleChange}
                placeholder="e.g. 1200"
              />
              {errors.price && <p className="text-red-500 text-sm mt-1">{errors.price}</p>}
            </div>
            
            <div className="mb-4">
              <label htmlFor="description" className="form-label">Description</label>
              <textarea
                id="description"
                name="description"
                rows="3"
                className={`form-control ${errors.description ? 'border-red-500' : ''}`}
                value={formData.description}
                onChange={handleChange}
                placeholder="Describe your product or service..."
              ></textarea>
              {errors.description && <p className="text-red-500 text-sm mt-1">{errors.description}</p>}
            </div>
            
            <div className="flex justify-end space-x-3">
              <button 
                type="button" 
                className="btn btn-outline" 
                onClick={() => {
                  setFormVisible(false)
                  resetForm()
                }}
              >
                Cancel
              </button>
              <button type="submit" className="btn btn-primary">
                {editingProduct ? 'Update Product' : 'Add Product'}
              </button>
            </div>
          </form>
        </div>
      )}

      {/* Products List */}
      {loading ? (
        <div className="text-center py-10">
          <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto"></div>
          <p className="mt-3 text-gray-500">Loading products...</p>
        </div>
      ) : products.length === 0 ? (
        <div className="text-center py-10 bg-white border rounded-lg">
          <svg className="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
          </svg>
          <h3 className="mt-2 text-lg font-medium text-gray-900">No products yet</h3>
          <p className="mt-1 text-gray-500">Get started by adding your first product or service.</p>
          <div className="mt-6">
            <button onClick={handleShowForm} className="btn btn-primary">
              Add Product
            </button>
          </div>
        </div>
      ) : (
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          {products.map(product => (
            <div key={product.id} className="bg-white rounded-lg shadow-md overflow-hidden">
              <div className="h-48 bg-gray-200">
                {product.image ? (
                  <img src={product.image} alt={product.name} className="w-full h-full object-cover" />
                ) : (
                  <div className="flex items-center justify-center h-full bg-gray-100">
                    <svg className="h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                  </div>
                )}
              </div>
              <div className="p-5">
                <h3 className="font-bold text-xl mb-2">{product.name}</h3>
                <div className="text-lg font-medium text-green-600 mb-2">R{product.price.toFixed(2)}</div>
                <p className="text-gray-700 mb-4">{product.description}</p>
                <div className="flex justify-between">
                  <button 
                    onClick={() => handleEdit(product)}
                    className="text-blue-600 hover:text-blue-800 flex items-center"
                  >
                    <svg className="h-5 w-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    Edit
                  </button>
                  <button 
                    onClick={() => handleDelete(product.id)}
                    className="text-red-600 hover:text-red-800 flex items-center"
                  >
                    <svg className="h-5 w-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                    Delete
                  </button>
                </div>
              </div>
            </div>
          ))}
        </div>
      )}
    </div>
  )
}

export default ManageProducts
