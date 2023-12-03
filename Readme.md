## Doc Tools

### Documenting Fusion

#### Summary and Description

```fusionlanguage
prototype(Neos.DocTools:Example.Description) {
    @doc.summary = "A short summary of the prototype"
    @doc.description = "
        A longer description. Often with some example code or further information.
        The description gets rendered below the summary and properties
    "
}
```

`@doc` can be used as a shorthand for `@doc.summary`

#### Simple `@doc`

To throw in a few sentences to document the prototype just `@doc` can be used.

```fusionlanguage
prototype(Neos.DocTools:Example.Simple) {
    @doc = "This prototype does things."
}
```

#### Properties

Properties get documented with `@doc` in the `@propType` definition. Whereas the *name*, *type* and its *requirement* get parsed from the `@propType` definition and the description text from `@doc`.
Per default only `@doc.summary` gets rendered into the documentation files. So just `@doc` can be used.

```fusionlanguage
prototype(Neos.DocTools:Example.Properties) {
    @propTypes {
        titles = ${PropTypes.arrayOf(PropTypes.string).isRequired}
        titles.@doc = "The array of titles"
    }
}
```

To document meta-properties (like `@glue` in `Neos.Fusion:Join`) the `@meta` property is used:

```fusionlanguage
prototype(Neos.DocTools:Example.MetaProperties) {
    @propTypes {
        @meta.glue = ${PropTypes.string}
        @meta.glue.@doc = "The glue used to join the items together"

        @meta.superGlue = ${PropTypes.string}
        @meta.superGlue.@doc = "The superGlue used to join the items together more strongly"
    }
}
```

#### Deprecations

Deprecated prototypes can be declared with the `@deprecated` meta-property:

```fusionlanguage
prototype(Neos.DocTools:Example.Deprecated) {
    @doc = "A short summary of the prototype"
    @deprecated = "This prototype is deprecated. Use Neos.DocTools:Example.NewAndShiny instead."
}
```

Properties can also be declared as deprecated. Even though they can currently not be rendered as such in the reference:

```fusionlanguage
prototype(Neos.DocTools:Example.MinorPropertyChange) {
    @doc = "A short summary of the prototype"
    @propTypes {
        title = ${PropTypes.string}
        title.@doc = "The Title"
        title.@deprecated = "`Title` should no longer be used. Use `name` instead"

        name = ${PropTypes.string}
        name.@doc = "The Name"
    }
}
```
