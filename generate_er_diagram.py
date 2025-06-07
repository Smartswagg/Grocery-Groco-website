from graphviz import Digraph

def er_diagram():
    dot = Digraph('ER_Diagram', filename='er_diagram_traditional_isa_green', format='pdf')
    dot.attr(rankdir='LR', dpi='300', splines='polyline', nodesep='0.001', ranksep='0.01')
    dot.attr('node', fontname='Helvetica-Bold')
    dot.attr('edge', fontname='Helvetica-Bold')

    # Entities and their attributes (address moved to Order)
    entities = {
        'User': ['user_id (PK)', 'name', 'email', 'password', 'phone'],
        'Customer': [],
        'Seller': [],
        'Admin': [],
        'Product': ['product_id (PK)', 'name', 'price', 'category_id (FK)', 'seller_id (FK)'],
        'Category': ['category_id (PK)', 'name'],
        'Order': ['order_id (PK)', 'customer_id (FK)', 'order_date', 'status', 'address'],
        'Order_Item': ['order_item_id (PK)', 'order_id (FK)', 'product_id (FK)', 'quantity', 'price'],
        'Cart': ['cart_id (PK)', 'customer_id (FK)'],
        'Cart_Item': ['cart_item_id (PK)', 'cart_id (FK)', 'product_id (FK)', 'quantity'],
        'Wishlist': ['wishlist_id (PK)', 'customer_id (FK)'],
        'Wishlist_Item': ['wishlist_item_id (PK)', 'wishlist_id (FK)', 'product_id (FK)'],
        'Review': ['review_id (PK)', 'product_id (FK)', 'customer_id (FK)', 'rating', 'comment', 'date'],
        'Message': ['message_id (PK)', 'user_id (FK)', 'admin_id (FK)', 'subject', 'content', 'date']
    }

    entity_color = '#e6f2ff'
    for entity, attrs in entities.items():
        dot.node(entity, entity, shape='rectangle', fontsize='38', width='1.1', height='0.6', fixedsize='false',
                 style='filled,bold', fillcolor=entity_color, color='#003366', penwidth='2')
        for attr in attrs:
            attr_node = f"{entity}_{attr}"
            dot.node(attr_node, attr, shape='ellipse', fontsize='34', width='0.8', height='0.4', fixedsize='false',
                     style='filled', fillcolor='#f9f9f9', color='#666666')
            dot.edge(entity, attr_node, arrowhead='none', color='#222222')

    # Relationships (diamonds) with compact size and large, readable font for labels
    def rel(name, from_, to, from_card, to_card):
        dot.node(name, name, shape='diamond', fontsize='34', width='0.8', height='0.4', fixedsize='false',
                 style='filled', fillcolor='#fff2cc', color='#b38f00', penwidth='2')
        dot.edge(from_, name, label=from_card, fontsize='30', fontname='Helvetica-Bold',
                 arrowhead='normal', arrowsize='2.5', color='#222222', penwidth='3')
        dot.edge(name, to, label=to_card, fontsize='30', fontname='Helvetica-Bold',
                 arrowhead='normal', arrowsize='2.5', color='#222222', penwidth='3')

    rel('places', 'Customer', 'Order', '1', 'M')
    rel('contains', 'Order', 'Order_Item', '1', 'M')
    rel('sells', 'Seller', 'Product', '1', 'M')
    rel('has', 'Product', 'Review', '1', 'M')
    rel('belongs_to', 'Product', 'Category', 'M', '1')
    rel('owns_cart', 'Customer', 'Cart', '1', '1')
    rel('cart_contains', 'Cart', 'Cart_Item', '1', 'M')
    rel('cartitem_product', 'Cart_Item', 'Product', 'M', '1')
    rel('owns_wishlist', 'Customer', 'Wishlist', '1', '1')
    rel('wishlist_contains', 'Wishlist', 'Wishlist_Item', '1', 'M')
    rel('wishlistitem_product', 'Wishlist_Item', 'Product', 'M', '1')
    rel('writes_review', 'Customer', 'Review', '1', 'M')
    rel('sends', 'Customer', 'Message', '1', 'M')
    rel('sends', 'Seller', 'Message', '1', 'M')
    rel('receives', 'Message', 'Admin', 'M', '1')
    rel('manages_users', 'Admin', 'User', '1', 'M')
    rel('manages_products', 'Admin', 'Product', '1', 'M')
    rel('manages_orders', 'Admin', 'Order', '1', 'M')
    rel('manages_categories', 'Admin', 'Category', '1', 'M')
    rel('manages_messages', 'Admin', 'Message', '1', 'M')

    # ISA (Specialization) with green triangle and label inside it
    dot.node('ISA_TRI', 'ISA', shape='triangle', width='1.1', height='0.6', style='filled', fillcolor='#b3e6b3', color='#267326', penwidth='2', fontsize='38', fontname='Helvetica-Bold')
    dot.edge('User', 'ISA_TRI', arrowhead='none', color='#222222', penwidth='2')
    for sub in ['Customer', 'Seller', 'Admin']:
        dot.edge('ISA_TRI', sub, arrowhead='none', color='#222222', penwidth='2')

    dot.render(view=True, cleanup=True)

if __name__ == '__main__':
    er_diagram()