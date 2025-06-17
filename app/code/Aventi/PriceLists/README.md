Aventi Price Lists for Magento 2, allows you to simply create different categorisations of prices, based on customer and/or customer groups, allowing you to give specific customers, specific products at a specific price.
Simply login the admin after insalling and Go to Aventi Solutions -> Price Lists, and Click add new.

Fill in the simple form, and magento the rest will be taken care off.

Its composed highly of plugins no core overrides, so you can safely continue to dev, without risk of overwritting,

We have also made sure to store the original data in extension attribute at the point of product getById and get and getList on search results, so you can always access it to modify it further.

You can now configure the module how you so desire,

Under System / Configuration / Aventi Solutions / Price Lists
- Enable Or Disable the module
- Restrict the categories / products same option, from customers not in the price lists
- Replace the add to cart form template if the customer is not in the list, its not restricted and you do not wish them to buy untill they have a quote
- The lowest price from all lists is now used to give the price.
- You can completly restrict the entire list from even showing the products at all, and redirect from the product if directly accessed
